# === Charigame ===
Contributors: ([Christian Krenn](https://github.com/christiankrenn))
Tags: wordpress-plugin, wp_scheduled_tasks, browsergame, carbonfields, gutenberg

Charigame is a versatile fundraising plugin designed to streamline your donation campaigns with ease. With the ability to run multiple campaigns concurrently, this plugin empowers you to manage diverse fundraising initiatives effortlessly.

## == Installation ==
npm install
Install foreman gem globally: gem install foreman
Development
Everything is stored within the ./src folder. Global Assets are in the ./assets/folder.

foreman start -f Procfile.dev
Deployment
Build WordPress blocks, tailwindcss and frontend scripts. Tailwindcss and frontend scripts go to ./dist folder, while WordPress blocks go to ./build folder.

foreman start
