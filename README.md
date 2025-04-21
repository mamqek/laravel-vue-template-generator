# Laravel Vue Template Generator

A generator tool for quickly scaffolding Laravel and Vue.js projects with built-in support for authentication, i18n language support, Vue Router, Pinia store, and optional integrations like TailwindCSS, ShadCN-Vue components, and Google OAuth.
Unlike many other templates for combining these frameworks, this one doesn't include Intertia, instead it passes all requests through Laravel routing and uses Axios for API requests.

## Features

- **Laravel + Vue.js**: Combines the power of Laravel for backend development with Vue.js for frontend development.
- **Authentication**: Pre-configured authentication setup.
- **i18n Language Support**: Easily manage translations and localization.
- **Vue Router**: Pre-configured routing for Vue.js.
- **Pinia Store**: State management with Pinia, including persisted state support.
- **TailwindCSS**: Optional integration with TailwindCSS (v3 or v4).
- **ShadCN-Vue Components**: Optional integration with ShadCN-Vue components.
- **Google OAuth**: Optional setup for Google OAuth authentication.

## Installation

To install the generator globally, run:

```bash
npm install -g laravel-vue-generator
```

## Usage
Run the generator using the following command:

```bash
npx laravel-vue-generate
```

The generator will prompt you for the following options:

- Project name
- Whether to include TailwindCSS (and which version: v3 or v4)
- Whether to use ShadCN-Vue components
- Whether to initialize an SQLite database
- Whether to set up Google OAuth

## Project Structure

The generated project will have the following structure:

```
project-name/
├── .env.example
├── .gitattributes
├── .gitignore
├── artisan
├── composer.json
├── jsconfig.json
├── package.json
├── phpunit.xml
├── README.md
├── vite.config.js
├── resources/
│   ├── css/
│   │   └── tailwind.css
│   ├── js/
│   │   ├── app.js
│   │   ├── plugins/
│   │   │   ├── axios.js
│   │   │   ├── lang.js
│   │   │   └── pinia.js
│   │   ├── router.js
│   │   └── stores/
│   │       └── userStore.js
│   └── views/
├── database/
│   └── database.sqlite (if SQLite is selected)
├── public/
│   └── images/
└── ...
```

## Options

### TailwindCSS
Choose between TailwindCSS v3 or v4.
Includes pre-configured tailwind.config.js and resources/css/tailwind.css.

### ShadCN-Vue Components
Optionally add ShadCN-Vue components to your project.
Includes example components and configuration.

### Google OAuth
Optionally set up Google OAuth using Laravel Socialite.

### SQLite
Optionally initialize an SQLite database and run migrations.

## Development
To start the development server, navigate to your project directory and run:

```bash
npm run dev
```

## Contributing
Contributions are welcome! Feel free to open an issue or submit a pull request.

## License
This project is licensed under the ISC License. See the [LICENSE](./LICENSE) file for details.