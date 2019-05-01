#! /bin/sh

deploy_host='157.230.214.253'

cd react_chess
sed -i "s/localhost:8000/$deploy_host/" src/components/Main.js
npm run build
# sed -i "s/$deploy_host/localhost:8000/" src/components/Main.js
cd ..
cp -r react_chess/build/* /var/www/html
cp -r php/* /var/www/html
