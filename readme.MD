# Pokemons REST API Exercise

## Local installation
- You will need Docker
- Run `docker compose up`
- Install dependencies with Composer
  - `docker exec pokemons-php-1 composer install`
  - `docker exec pokemons-php-1 composer dump-autoload`
## How to test
If using Postman, import the collection from provided file
Pokemons.postman_collection.json
With Curl or Postman, test those calls
  - GET http://0.0.0.0:8080/index.php/rest/pokemons?limit=10&page=1
  - GET http://0.0.0.0:8080/index.php/rest/pokemons/{id}
  - GET http://0.0.0.0:8080/index.php/rest/pokemons/{name}
  - POST http://0.0.0.0:8080/index.php/rest/pokemons
  - PUT http://0.0.0.0:8080/index.php/rest/pokemons/{name}
  - DELETE http://0.0.0.0:8080/index.php/rest/pokemons/{name}

## (Many) Possible improvements 
To make sure this is a professional project, I would add those elements :
- Proper REST APIs need OAuth!!!
- As many calls can happen at the same time, to ensure stability of the application, do one of the following :
  - Add a .lock file system to actually lock the csv file while editing it to prevent conflicts
  - Add a messaging queue system like RabbitMQ
- Validate input types for every single field (make sure HP is integer for example)
- Nice to have Swagger or ReDoc documentation so the client can easily expects what calls he can do, what parameters to send and what kind of responses he could have
- Permissions : Some users could access only GET but no POST/PUT/DELETE