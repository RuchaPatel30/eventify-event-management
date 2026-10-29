EVENTIFY — VERCEL DEPLOYMENT PACKAGE (PHP + MySQL)

IMPORTANT
- Vercel does not provide a built-in MySQL database. You must use a reachable external MySQL provider.
- PHP support here uses the community runtime vercel-php@0.9.0, not an official Vercel PHP runtime.
- Do not commit database passwords to GitHub. Set them as Vercel Environment Variables.

FILES
- api/*.php: PHP pages/backend handlers
- public/style.css: frontend stylesheet
- database.sql: schema + sample event data; import it into the hosted database you created
- vercel.json: PHP runtime and page routing

DEPLOY STEPS
1. Create a hosted MySQL database with a provider that permits connections from Vercel/serverless apps.
2. Open that provider's phpMyAdmin/SQL console or MySQL client and select the created database.
3. Import database.sql. It intentionally does not create/select the database; select your hosted database first.
4. Upload this folder to a GitHub repository (the files in this folder should be at the repository root).
5. In Vercel, Add New > Project, import the GitHub repository, and deploy.
6. In Vercel Project Settings > Environment Variables, add:
   DB_HOST = hosted database hostname (not localhost)
   DB_NAME = exact database name
   DB_USER = database username
   DB_PASSWORD = database password
   DB_PORT = database port (usually 3306)
   Apply to Production (and Preview if needed), then redeploy.
7. Open the Vercel deployment URL and test browsing and making a booking.

NOTES
- Database provider must allow external/serverless connections and may require TLS or IP allow-list settings. Follow its connection instructions.
- Vercel deployments are serverless; do not use local files as a persistent database.
- This is a learning/demo project. My Bookings looks up reservations by email and is not secure account authentication. Do not use for sensitive/production bookings without additional security.
- Sample event dates are in 2027.
