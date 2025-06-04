help:
	@egrep "^# target" Makefile

# target: help                            - Display this help message
# target: docker-build|db                 - Setup/Build PHP & (node)JS dependencies
db: docker-build
docker-build: build-back

# target: build-back                      - Install PHP dependencies using Composer
build-back:
	docker-compose run --rm php sh -c "composer install"

# target: build-back-prod                 - Install PHP dependencies for production
build-back-prod:
	docker-compose run --rm php sh -c "composer install --no-dev -o"

# target: build-zip                       - Create a zip file of the project excluding certain files
build-zip:
	cp -Ra $(PWD) /tmp/ps_googleanalyticszaraz
	rm -rf /tmp/ps_googleanalyticszaraz/.docker
	rm -rf /tmp/ps_googleanalyticszaraz/.devcontainer
	rm -rf /tmp/ps_googleanalyticszaraz/.env.test
	rm -rf /tmp/ps_googleanalyticszaraz/.php_cs*
	rm -rf /tmp/ps_googleanalyticszaraz/.travis.yml
	rm -rf /tmp/ps_googleanalyticszaraz/cloudbuild.yaml
	rm -rf /tmp/ps_googleanalyticszaraz/composer.*
	rm -rf /tmp/ps_googleanalyticszaraz/package.json
	rm -rf /tmp/ps_googleanalyticszaraz/.npmrc
	rm -rf /tmp/ps_googleanalyticszaraz/package-lock.json
	rm -rf /tmp/ps_googleanalyticszaraz/.gitignore
	rm -rf /tmp/ps_googleanalyticszaraz/.editorconfig
	rm -rf /tmp/ps_googleanalyticszaraz/.git
	rm -rf /tmp/ps_googleanalyticszaraz/.github
	rm -rf /tmp/ps_googleanalyticszaraz/tests
	rm -rf /tmp/ps_googleanalyticszaraz/docker-compose.yml
	rm -rf /tmp/ps_googleanalyticszaraz/Makefile
	mv -v /tmp/ps_googleanalyticszaraz $(PWD)/ps_googleanalyticszaraz
	zip -r ps_googleanalyticszaraz.zip ps_googleanalyticszaraz
	rm -rf $(PWD)/ps_googleanalyticszaraz

# target: build-zip-prod                  - Launch prod zip generation of the module (will not work on windows)
build-zip-prod: build-back-prod build-zip

# target: php-cs-fixer                    - Run PHP CS Fixer in dry-run mode
php-cs-fixer:
	docker-compose run --rm php sh -c "php vendor/bin/php-cs-fixer fix --dry-run"

.PHONY: clean
# target: clean                           - Remove Docker and other custom folders/files
clean:
	rm -rf Docker
