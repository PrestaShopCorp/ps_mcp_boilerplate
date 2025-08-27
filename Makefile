SHELL=/bin/bash -o pipefail
MODULE_NAME = ps_mcp_tools
VERSION ?= $(shell git describe --tags 2> /dev/null || echo "v0.0.0")
SEM_VERSION ?= $(shell echo ${VERSION} | sed 's/^v//')
BRANCH_NAME ?= $(shell git rev-parse --abbrev-ref HEAD | sed -e 's/\//_/g')
PACKAGE ?= ${MODULE_NAME}-${VERSION}
PS_VERSION ?= 8.1.6
TESTING_IMAGE ?= prestashop/prestashop-flashlight:${PS_VERSION}
PS_ROOT_DIR ?= $(shell pwd)/prestashop/prestashop-${PS_VERSION}
WORKDIR ?= ./

export _PS_ROOT_DIR_ ?= ${PS_ROOT_DIR}
export PATH := ./vendor/bin:./devtools/vendor/bin:$(PATH)

# target: (default)                                            - Build the module
default: build

# target: build                                                - Setup PHP & Node.js locally
.PHONY: build
build: vendor devtools/vendor

# target: help                                                 - Get help on this file
.PHONY: help
help:
	@echo -e "##\n# ${MODULE_NAME}:\n#  version: ${VERSION}\n#  branch:  ${BRANCH_NAME}\n##"
	@egrep "^# target" Makefile

# target: clean                                                - Clean up the repository
.PHONY: clean
clean:
	git clean -fdX --exclude="!.npmrc" --exclude="!.env*"

# target: zip                                            - Bundle a production zip
.PHONY: zip
zip: vendor devtools/vendor dist
	@$(call zip_it,${PACKAGE}.zip)

dist:
	@mkdir -p ./dist

composer.phar:
	@php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');";
	@php composer-setup.php;
	@php -r "unlink('composer-setup.php');";

vendor: composer.phar
	./composer.phar install --no-dev -o;

devtools/vendor: composer.phar vendor
	./composer.phar install --working-dir devtools -o;

prestashop:
	@mkdir -p ./prestashop

prestashop/prestashop-${PS_VERSION}: prestashop composer.phar
	@if [ ! -d "prestashop/prestashop-${PS_VERSION}" ]; then \
		git clone --depth 1 --branch ${PS_VERSION} https://github.com/PrestaShop/PrestaShop.git prestashop/prestashop-${PS_VERSION} > /dev/null; \
		if [ "${PS_VERSION}" != "1.6.1.24" ]; then \
			./composer.phar -d ./prestashop/prestashop-${PS_VERSION} install; \
    fi \
	fi;

# target: test                                                 - Static and unit testing
.PHONY: test
test: composer-validate lint php-lint phpstan translation-validate

# target: docker-test                                          - Static and unit testing in docker
.PHONY: docker-test
docker-test: docker-lint docker-phpstan

# target: composer-validate (or docker-composer-validate)      - Validates composer.json and composer.lock
.PHONY: composer-validate
composer-validate: vendor
	@./composer.phar validate --no-check-publish
docker-composer-validate:
	@$(call in_docker,make,composer-validate)

# target: lint (or docker-lint)                                - Lint the code and expose errors
.PHONY: lint docker-lint
lint: php-cs-fixer php-lint
docker-lint: docker-php-cs-fixer docker-php-lint

# target: lint-fix (or docker-lint-fix)                        - Automatically fix the linting errors
.PHONY: lint-fix docker-lint-fix fix
fix: lint-fix
lint-fix: php-cs-fixer-fix
docker-lint-fix: docker-php-cs-fixer-fix

# target: php-cs-fixer (or docker-php-cs-fixer)                - Lint the code and expose errors
.PHONY: php-cs-fixer docker-php-cs-fixer  
php-cs-fixer: devtools/vendor
	@php-cs-fixer fix --dry-run --diff;
docker-php-cs-fixer: devtools/vendor
	@$(call in_docker,make,lint)

# target: php-cs-fixer-fix (or docker-php-cs-fixer-fix)        - Lint the code and fix it
.PHONY: php-cs-fixer-fix docker-php-cs-fixer-fix
php-cs-fixer-fix: devtools/vendor
	@php-cs-fixer fix
docker-php-cs-fixer-fix: devtools/vendor
	@$(call in_docker,make,lint-fix)

# target: php-lint (or docker-php-lint)                        - Lint the code with the php linter
.PHONY: php-lint docker-php-lint
php-lint:
	@find . -type f -name '*.php' -not -path "./vendor/*" -not -path "./devtools/*" -not -path "./prestashop/*" -print0 | xargs -0 -n1 php -l -n | (! grep -v "No syntax errors" );
	@echo "php $(shell php -r 'echo PHP_VERSION;') lint passed";
docker-php-lint:
	@$(call in_docker,make,php-lint)

# target: phpstan (or docker-phpstan)                          - Run phpstan
.PHONY: phpstan docker-phpstan
phpstan: devtools/vendor prestashop/prestashop-${PS_VERSION}
	phpstan analyse --memory-limit=-1 --configuration=./tests/phpstan/phpstan-local.neon;
docker-phpstan: devtools/vendor
	@$(call in_docker,/usr/bin/phpstan,analyse --memory-limit=-1 --configuration=./tests/phpstan/phpstan-docker.neon)

docker-phpstan-1-6: devtools/vendor
	@$(call in_docker_1_6,/usr/bin/phpstan,analyse --memory-limit=-1 --configuration=./modules/${MODULE_NAME}/tests/phpstan/phpstan-docker.neon)

# target: header-stamp                                         - check Headers of PHP files
.PHONY:header-stamp
header-stamp:
	devtools/vendor/bin/header-stamp --license=devtools/vendor/prestashop/header-stamp/assets/osl3.txt --exclude=vendor,devtools,e2e,e2e-env,tests,composer.json,scoper.inc.php

define replace_version
	echo "Setting up version: ${VERSION}..."
	sed -i.bak -e "s/\(VERSION = \).*/\1\'${2}\';/" ${1}/${MODULE_NAME}.php
	sed -i.bak -e "s/\($this->version = \).*/\1\'${2}\';/" ${1}/${MODULE_NAME}.php
	sed -i.bak -e "s|\(<version><!\[CDATA\[\)[0-9a-z.-]\{1,\}]]></version>|\1${2}]]></version>|" ${1}/config.xml
	rm -f ${1}/${MODULE_NAME}.php.bak ${1}/config.xml.bak
endef

define create_module
	$(eval TMP_DIR := $(shell mktemp -d))
	mkdir -p ${TMP_DIR}/${MODULE_NAME};
	@cp -r $(shell cat .zip-contents) ${TMP_DIR}/${MODULE_NAME} 2>/dev/null || true
	$(call replace_version,${TMP_DIR}/${MODULE_NAME},${SEM_VERSION})
	./devtools/vendor/bin/autoindex prestashop:add:index ${TMP_DIR}
	devtools/vendor/bin/header-stamp --target=${TMP_DIR}/${MODULE_NAME} --license=devtools/vendor/prestashop/header-stamp/assets/osl3.txt --exclude=vendor,e2e,e2e-env,tests,composer.json,scoper.inc.php
	cd ${TMP_DIR}/${MODULE_NAME} && composer dump-autoload
endef

define zip_it
	TMP_DIR=$(call create_module)
	cd ${TMP_DIR} && zip -9 -r $1 ./${MODULE_NAME};
	mv ${TMP_DIR}/$1 ./dist;
	rm -rf ${TMP_DIR};
endef

define in_docker
	docker run \
	--rm \
	--workdir /var/www/html/modules/${MODULE_NAME} \
	--volume $(shell pwd):/var/www/html/modules/${MODULE_NAME}:rw \
	--entrypoint $1 ${TESTING_IMAGE} $2
endef

define in_docker_1_6
	docker run --rm \
	--volume $(shell pwd):/var/www/html/modules/${MODULE_NAME}:rw \
	--entrypoint $1 ${TESTING_IMAGE} $2
endef
