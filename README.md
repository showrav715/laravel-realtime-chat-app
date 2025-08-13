# Vue + Laravel Real-Time Chat Application

A modern chat application built with Laravel 12+ (backend) and Vue 3 (frontend) with real-time messaging, photo uploads, and typing indicators, powered by Laravel Reverb.

## Features

- Real-time chat between users
- Upload photos along with messages
- Typing indicators for other users
- Auto-scroll messages to the bottom
- Real-time updates using Laravel Reverb

## Requirements

- PHP >= 8.2
- Laravel >= 12.x
- MySQL
- Node.js & npm
- Composer
- Laravel Reverb

## Installation and Setup

Clone the repository and move into the project folder:

`git clone https://github.com/yourusername/chat-app.git && cd chat-app`

Install backend and frontend dependencies with Composer and npm:

`composer install && npm install`

Copy the example environment file and generate an app key:

`cp .env.example .env && php artisan key:generate`

Update your `.env` file with database credentials and Laravel Reverb details:

`DB_DATABASE=chatapp`
`DB_USERNAME=root`
`DB_PASSWORD=password`
`REVERB_APP_ID=your_app_id`
`REVERB_APP_KEY=your_app_key`
`REVERB_APP_SECRET=your_app_secret`
`REVERB_HOST=127.0.0.1`
`REVERB_PORT=8080`
`REVERB_SCHEME=http`

Install the broadcasting driver:

`php artisan install:broadcasting` and choose **Laravel Reverb** when prompted.

Run database migrations and start the queue worker:

`php artisan migrate && php artisan queue:work`

Start the Reverb server:

`php artisan reverb:serve`

Serve the Laravel app and build frontend assets:

`php artisan serve && npm run dev`

## Usage

Open the application in your browser at `http://localhost:8000`, start chatting with other users in real-time, upload photos with messages, and see typing indicators for active users.

## Tech Stack

Backend: Laravel 12+  
Frontend: Vue 3 + Composition API  
Real-time: Laravel Echo + Laravel Reverb  
Database: MySQL  

