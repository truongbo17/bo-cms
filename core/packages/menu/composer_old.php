{
    "name": "bo/menu",
    "type": "package",
    "description": "An admin panel for menu items, using Backpack\\CRUD on Laravel 6.",
    "require": {
        "backpack/pagemanager": "^3.0|^2.0"
    },
    "require-dev": {
        "phpunit/phpunit" : "^9.0||^7.0",
        "scrutinizer/ocular": "~1.1",
        "squizlabs/php_codesniffer": "~2.3 || ~3.0"
    },
    "autoload": {
        "psr-4": {
            "Backpack\\MenuCRUD\\": "src"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Backpack\\MenuCRUD\\": "tests"
        }
    },
    "scripts": {
        "test": "vendor/bin/phpunit --testdox"
    },
    "extra": {
        "branch-alias": {
            "dev-master": "1.0-dev"
        },
        "laravel": {
            "providers": [
                "Backpack\\MenuCRUD\\MenuCRUDServiceProvider"
            ]
        }
    }
}
