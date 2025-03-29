# WEBAPP - Wishlist management website

## Introduction

This project involves developing a website to create and manage wishlists and allow users to purchase gifts for others. The site is developed with Symfony and follows the IMT Atlantique graphical charter.

## Prerequisites

Before starting, make sure you have the following installed on your machine:
- PHP 8.x
- Composer
- Symfony CLI
- Docker

## Project Structure

The project is structured as follows:

```
conception             # Conception documents
server                 # Symfony server
    ├── config         # Configuration files
    ├── public         # Public files (CSS, JS, images)
    ├── migrations     # Database migrations
    ├── src            # Source code
    └── templates      # Twig templates
```

## Installation

1. Clone the repository

```
git clone https://gitlab-df.imt-atlantique.fr/t22icard/webapp-equipe7.git
```

2. Navigate to the project directory

```
cd server
```

3. Install dependencies

```
composer install
```

4. Start the development environment

```
docker compose up -d
```

5. Run migrations

```
php bin/console doctrine:migrations:migrate
```

6. Load fixtures (optional)

The fixtures are used to populate the database with initial data. You can load them using the following command:

```
php bin/console doctrine:fixtures:load
```

7. Launch the development server

```
symfony server:start
```

## Usage

Once the server is running, you can access the application at `http://localhost:8000`.

Accounts for testing:

- user1@example.com (Admin account)
  - Email: `user1@example.com`
  - Password: `password123`
- user2@example.com
    - Email: `user2@example.com`
    - Password: `password123`
- user3@example.com
    - Email: `user3@example.com`
    - Password: `password123`
- user4@example.com
    - Email: `user4@example.com`
    - Password: `password123`
- user5@example.com
    - Email: `user5@example.com`
    - Password: `password123`

The development Docker Compose provides a test email server. You can check the emails sent by the application at `http://localhost:8025`. This is useful for testing email notifications and other email-related features of the application.

## Credits

This project was developed by the following team members:

- Duo 19
  - Julie Descloitres
  - Jean-Philippe Levesques
- Duo 20
  - Auguste Celerier
  - Éric Khella
- Duo 21
  - Yiré Soro
  - Tchadel Icard