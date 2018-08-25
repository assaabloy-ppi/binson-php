.DEFAULT_GOAL := help
.PHONY: any

help: Makefile
	@echo "\nUsage: make <target>"
	@echo "\twhere <target> is one of the following:\n"
	@grep -E '^[0-9a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST)  | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
	@echo


all: build

build: ## Build `binson` PHP extension	
	mkdir -p ./out
	./swig/swig -debug-tmsearch -php7 -outdir ./out -o ./out/binson_light_wrap.c ./binson-c-light/api/binson_light.i
	cd out && gcc `php-config --includes` -I../binson-c-light -I. -fpic -DWITH_TO_STRING=1 -c \
		binson_light_wrap.c ../binson-c-light/binson_light.c 
	gcc -shared ./out/*.o -o ./out/binson.so
	@echo -e '\n\n=========== Trying to load new module ===='
	php -dextension=./out/binson.so -i -m | grep binson
	@echo =========== 

total_clean: clean ## Project total cleanup (also deletes depencencies & its sources)
	rm -rf swig


clean: ## Project cleanup
	rm -rf binson-c-light out

total_rebuild: ## Cleanup, download dependencies and build `binson` PHP extension
	$(MAKE) total_clean
	git clone https://github.com/assaabloy-ppi/binson-c-light.git
	git clone https://github.com/swig/swig.git
	cd swig && ./autogen.sh && ./configure && make
	$(MAKE) all

rebuild: ## Cleanup and rebuild `binson` PHP extension
	$(MAKE) clean
	$(MAKE) all


test: ## Run unit tests for 'ext' subproject
	php -dextension=./out/binson.so  ../vendor/bin/phpunit -v --testdox tests	