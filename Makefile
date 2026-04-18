.PHONY: deploy pull composer-install migrate help

help:
	@echo "Targets:"
	@echo "  deploy            Full deploy: pull, composer (runs npm via post-install-cmd), migrate"
	@echo "  pull              git pull"
	@echo "  composer-install  composer install --no-dev --optimize-autoloader"
	@echo "  migrate           Apply Doctrine migrations"

deploy: pull composer-install migrate
	@echo "Deploy complete"

pull:
	git pull

composer-install:
	composer install --no-dev --optimize-autoloader

migrate:
	php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
