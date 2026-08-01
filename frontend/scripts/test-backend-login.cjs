const http = require('http');

const data = JSON.stringify({
  email: 'gestionnaire@autochain.test',
  password: 'password',
});

const options = {
  hostname: '127.0.0.1',
  port: 8000,
  path: '/api/login',
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Content-Length': Buffer.byteLength(data),
  },
};

const req = http.request(options, (res) => {
  let body = '';
  res.on('data', (chunk) => {
    body += chunk;
  });
  res.on('end', () => {
    console.log('status', res.statusCode);
    console.log(body);
  });
});

req.on('error', (error) => {
  console.error('error', error.message);
});

req.write(data);
req.end();
