const { spawn } = require('child_process');
const path = require('path');
const fs = require('fs');

const backendDir = path.join(__dirname, '..', 'backend');
let phpExe = 'php';

if (process.platform === 'win32') {
  if (fs.existsSync('C:\\xampp\\php\\php.exe')) phpExe = 'C:\\xampp\\php\\php.exe';
  else if (fs.existsSync('C:\\laragon\\bin\\php')) {
    const dirs = fs.readdirSync('C:\\laragon\\bin\\php');
    const phpDir = dirs.find(d => d.startsWith('php-'));
    if (phpDir) phpExe = path.join('C:\\laragon\\bin\\php', phpDir, 'php.exe');
  }
}

const child = spawn(phpExe, ['-S', 'localhost:8888', '-t', backendDir], {
  stdio: 'inherit',
  shell: true,
  cwd: path.join(__dirname, '..')
});

child.on('error', (err) => {
  console.error('Failed to start backend:', err.message);
  console.error('Run backend\\start-server.bat manually, then npm start.');
  process.exit(1);
});
child.on('exit', (code) => process.exit(code || 0));
