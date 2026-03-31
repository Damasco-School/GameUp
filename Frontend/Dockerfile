FROM nginx:alpine

COPY index.html   /usr/share/nginx/html/index.html
COPY create.html  /usr/share/nginx/html/create.html
COPY play.html    /usr/share/nginx/html/play.html
COPY forum.html   /usr/share/nginx/html/forum.html

RUN printf 'server {\n    listen 80;\n    root /usr/share/nginx/html;\n    index index.html;\n    location / {\n        try_files $uri $uri/ /index.html;\n    }\n}\n' > /etc/nginx/conf.d/default.conf

EXPOSE 80
