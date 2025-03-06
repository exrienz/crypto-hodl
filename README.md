# crypto-hodl

docker build -t crypto-app:latest .

docker run -d -p 8080:80   -e DB_HOST=""   -e DB_USERNAME=""   -e DB_PASSWORD=""   -e DB_NAME=""   --name crypto-app-container   crypto-app:latest