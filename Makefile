.DEFAULT_GOAL := help
.PHONY: any

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
	php ./tests/5_Functional/FuzzyConsistancyTestStandalone.php