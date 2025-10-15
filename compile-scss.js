// Script di compilazione SCSS manuale
const sass = require('sass');
const fs = require('fs');
const path = require('path');

const srcFile = path.join(__dirname, 'assets/css/src/main.scss');
const distFile = path.join(__dirname, 'assets/css/dist/main.min.css');

try {
    console.log('Compilazione SCSS in corso...');
    const result = sass.compile(srcFile, {
        style: 'compressed',
        sourceMap: false
    });
    
    fs.writeFileSync(distFile, result.css);
    console.log('✅ CSS compilato con successo!');
    console.log(`📁 Output: ${distFile}`);
} catch (error) {
    console.error('❌ Errore compilazione:', error.message);
    process.exit(1);
}
