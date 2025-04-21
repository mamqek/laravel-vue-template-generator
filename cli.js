#!/usr/bin/env node
import { createEnv } from 'yeoman-environment';
import path from 'path';
import { createRequire } from 'module';
import { fileURLToPath } from 'url';

const require = createRequire(import.meta.url);
const env = createEnv();

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

env.register(
  path.join(__dirname, 'generators', 'app', 'index.js'),
  'myapp'
);

// Run the generator
const args = process.argv.slice(2);
const generatorName = args[0] || 'myapp'; // Default to 'myapp' if no argument is provided

env.run(generatorName, (err) => {
    if (err) {
        console.error('Error running the generator:', err);
        process.exit(1);
    }
});