# TechPromux Base Bundle: Main Core bundle for TechPromux solutions bundles

This project is a symfony based bundle with multiples customized elements.

It integrates well known projects like Sonata Admin, Sonata User, FOS User, FOS Rest and others dependencies.

It provides a custom and extended supports for anothers TechPromux implemented solutions.

You only need download it and use it with a little effort. 

We hope that this project contribute to your work with Symfony.

# Instalation
-----------------

Open a console in root project folder and execute following command:

    composer install techpromux/base-bundle

# Configuration
-----------------

For custom database and other options edit files:

	// TODO

Create/Update tables from entities definitions, executing following command:

    ./bin/console doctrine:schema:update --force


Force bundle to copy all assets in public folder, executing following command:

    ./bin/console assets:install web (for Symfony <= 3.3)

    ./bin/console assets:install public (for Symfony >= 3.4)
