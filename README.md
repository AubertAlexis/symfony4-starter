# Symfony4 Starter

## Guide
This is a simple starter who can be used for any project with back-office.

This starter has many features :
* Login -> check your roles and redirect ( ROLE_ADMIN -> BO | ROLE_USER -> FO)
* Registration form
* 

## Installation

### 1 - Composer

`composer install` :
Will download the `dependencies` needed for the project

### Database

IMPORTANT : change the .env with your information.

Create the database :
`php bin/console doctrine:database:create`

Update the database :
`php bin/console doctrine:migration:migrate`

### Fixtures

Load random fake data.
`php bin/console doctrine:fixtures:load`

### Server

Launch server.
`php bin/console server:run`
