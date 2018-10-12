.DEFAULT_GOAL := help
.PHONY: any

ifeq ($(OS),Windows_NT)
    OPEN := start
else
    UNAME := $(shell uname -s)
    ifeq ($(UNAME),Linux)
        OPEN := xdg-open
    endif
endif

help: Makefile
	@echo "\nUsage: make <target>"
	@echo "\twhere <target> is one of the following:\n"
	@grep -E '^[0-9a-zA-Z_-.%]+:.*?## .*$$' $(MAKEFILE_LIST)  | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
	@echo


init: ## Initialize environment
	mkdir composer
	curl -sS https://getcomposer.org/installer -o composer/composer-setup.php && \
	php composer/composer-setup.php --install-dir=composer --filename=composer

update: ## Update current project dependencies
	php composer/composer update

test.all: ## Run ALL unit & functional tests for 'binson-php' project
	vendor/bin/phpunit -v --bootstrap ./tests/bootstrap_native.phpb --testdox tests

test.%: ## Run some tests, specified by group filter: [lowlevel|writer|parser|serializer|deserializer|functional|fuzzy]
	vendor/bin/phpunit -v --bootstrap ./tests/bootstrap_native.phpb --testdox tests --group $(*)

test.overnight: ## Run some heavy fuzzy tests in continuous mode
	php ./tests/6_Functional/FuzzyConsistancyTestStandalone.php

test.c: ## Run verification tests against test data located in 'binson-c-light' repository
	mkdir -p temp && cd temp && \
	(git -C binson-c-light pull || git clone https://github.com/assaabloy-ppi/binson-c-light.git) && \
	php ../tests/6_Functional/VerifyTestVectorDir.php ./binson-c-light/utest/test_data valid  && \
	php ../tests/6_Functional/VerifyTestVectorDir.php ./binson-c-light/utest/test_data invalid


coverage: ## Generate code coverage report and open browser for viewing it
	vendor/bin/phpunit --coverage-html ./report/cov --whitelist \
	./src/binson.php -v --bootstrap ./tests/bootstrap_native.phpb --testdox tests \
	|| $(OPEN) report/cov/index.html
