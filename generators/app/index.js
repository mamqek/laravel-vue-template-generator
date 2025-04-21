import path from 'path';
import { fileURLToPath } from 'url';
import Generator from 'yeoman-generator';
import fs from 'fs';

// Resolve __dirname in ESM
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

export default class AppGenerator extends Generator {
    constructor(args, opts) {
        super(args, opts);
        // Point to our templates directory
        this.sourceRoot(path.join(__dirname, 'templates'));
    }

    async prompting() {
        this.answers = await this.prompt([
            {
                type: 'input',
                name: 'name',
                message: "What's your project name?",
                default: this.appname,
            },
            {
                type: 'confirm',
                name: 'tailwind',
                message: 'Include Tailwind CSS?',
                default: false,
            },
            {
                type: 'list',
                name: 'tailwindVersion',
                message: 'Which Tailwind CSS version?',
                choices: ['3', '4'],
                default: '3',
                when: answers => answers.tailwind,
            },
            {
                type: 'confirm',
                name: 'shadcn',
                message: 'Do you want to use shadcn-vue components?',
                default: false,
                when: answers => answers.tailwind,
            },
            {
                type: 'confirm',
                name: 'sqlite',
                message: 'Do you want to initialize SQLite db file?',
                default: false,
                when: answers => answers.tailwind,
            },
            {
                type: 'confirm',
                name: 'oauth',
                message: 'Setup Google OAuth?',
                default: false,
            },
        ]);
    }

    writing() {
        const { name, tailwind, tailwindVersion, shadcn, oauth } = this.answers;

        // Copy all base files including dotfiles
        this.fs.copy(
            this.templatePath('base'),
            this.destinationPath(name),
            { globOptions: { dot: true } }
        );

        if (tailwind) {
            // Overlay tailwind-specific files for chosen version
            this.fs.copy(
                this.templatePath(`tailwind-v${tailwindVersion}`),
                this.destinationPath(name),
                { globOptions: { dot: true } }
            );
        }

        // shadcn/ui
        if (shadcn) {
            this.fs.copy(
                this.templatePath(`shadcn-v${tailwindVersion}`),
                this.destinationPath(name),
                { globOptions: { dot: true } }
            );
        }

        // OAuth
        if (oauth) {
            this.fs.copy(
                this.templatePath(`oauth`),
                this.destinationPath(name),
                { globOptions: { dot: true } }
            );
        }

    }

    install() {
        const { sqlite, shadcn, tailwindVersion, oauth } = this.answers;

        const projectPath = this.destinationPath(this.answers.name);

        // Install PHP dependencies
        this.log(`\nInstalling PHP dependencies (composer) in ./${this.answers.name}…`);
        this.spawnCommandSync('composer', ['install'], { cwd: projectPath });

        // Install Node dependencies
        this.log(`\nInstalling Node dependencies (npm) in ./${this.answers.name}…`);
        this.spawnCommandSync('npm', ['install'], { cwd: projectPath });

        // Initialize environment using Node fs copy to be cross-platform
        this.log(`\nInitializing environment file…`);
        try {
            fs.copyFileSync(
                path.join(projectPath, '.env.example'),
                path.join(projectPath, '.env')
            );
            this.spawnCommandSync('php', ['artisan', 'key:generate'], { cwd: projectPath });
        } catch (err) {
            this.log('⚠️  Could not copy .env file:', err.message);
        }

        if (sqlite) {
            // Initialize database
            this.log(`\nSetting up SQLite database…`);
            if (process.platform === 'win32') {
                this.spawnCommandSync('powershell', [
                    'New-Item',
                    '-Path', './database/database.sqlite',
                    '-ItemType', 'File'
                ], { cwd: projectPath });
            } else {
                this.spawnCommandSync('touch', ['database/database.sqlite'], { cwd: projectPath });
            }

            // Populate database
            this.log(`\nMigrating and seeding database…`);
            this.spawnCommandSync('php', ['artisan', 'migrate'], { cwd: projectPath });
            this.spawnCommandSync('php', ['artisan', 'db:seed'], { cwd: projectPath });
        }

        if (shadcn) {
            if (tailwindVersion === '3') {
                this.log(`\nInstalling shadcn-vue dependencies (npm) in ./${this.answers.name}…`);
                this.spawnCommandSync('npx', ['shadcn-vue@1.0.3', 'add', 'switch'], { cwd: projectPath });
            } else {
                this.log(`\nInstalling shadcn-vue example component in ./${this.answers.name}/resources/js/ui/shadcn-vue…`);
                this.spawnCommandSync('npx', ['shadcn-vue@latest', 'add', 'switch'], { cwd: projectPath });
            }
        }

        if (oauth) {
            this.log(`\nInstalling Google OAuth dependency (laravel/socialite) in ./${this.answers.name}…`);
            this.spawnCommandSync('composer', ['require', 'laravel/socialite:^5.19'], { cwd: projectPath });
        }
    }

    end() {
        this.log(
            `\n✅ Created ${this.answers.name}${this.answers.tailwind ? ' with Tailwind' : ''}!`
        );
    }
}
