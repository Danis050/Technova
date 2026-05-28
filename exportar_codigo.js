const fs = require('fs');
const path = require('path');

const ROOT_DIR = process.cwd();
const OUTPUT_FILE = path.join(ROOT_DIR, 'codigo_fuente.txt');
const EXTENSIONS = new Set(['.html', '.php', '.css']);
const EXCLUDED_DIRS = new Set(['node_modules', '.git']);

function collectFiles(dir) {
  const entries = fs.readdirSync(dir, { withFileTypes: true });
  const results = [];

  for (const entry of entries) {
    const fullPath = path.join(dir, entry.name);

    if (entry.isDirectory()) {
      if (!EXCLUDED_DIRS.has(entry.name)) {
        results.push(...collectFiles(fullPath));
      }
      continue;
    }

    const ext = path.extname(entry.name).toLowerCase();
    if (EXTENSIONS.has(ext)) {
      results.push(fullPath);
    }
  }

  return results;
}

function buildOutput(files) {
  const sections = files
    .sort((a, b) => a.localeCompare(b))
    .map((filePath) => {
      const relativePath = path.relative(ROOT_DIR, filePath);
      const content = fs.readFileSync(filePath, 'utf8');

      return [
        '============================================================',
        `Archivo: ${relativePath}`,
        '============================================================',
        content,
        ''
      ].join('\n');
    });

  return sections.join('\n');
}

try {
  const files = collectFiles(ROOT_DIR);

  if (files.length === 0) {
    fs.writeFileSync(OUTPUT_FILE, 'No se encontraron archivos .html, .php o .css.\n', 'utf8');
    console.log('No se encontraron archivos para exportar.');
    process.exit(0);
  }

  const output = buildOutput(files);
  fs.writeFileSync(OUTPUT_FILE, output, 'utf8');

  console.log(`Exportación completada: ${path.basename(OUTPUT_FILE)}`);
  console.log(`Archivos incluidos: ${files.length}`);
} catch (error) {
  console.error('Error al exportar archivos:', error.message);
  process.exit(1);
}
