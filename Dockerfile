FROM php:8.2-cli

# Install dependencies (optional)
RUN apt-get update && apt-get install -y curl

# Set working directory
WORKDIR /app

# Copy all project files into the container
COPY . .

# Expose the port the PHP server will run on
EXPOSE 10000

# Start PHP built-in server
CMD ["php", "-S", "0.0.0.0:10000", "index.php"]
