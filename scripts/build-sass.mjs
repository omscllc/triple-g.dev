import { readdir, writeFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import * as sass from 'sass';

const projectRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const themeRoot = path.join(projectRoot, 'web/themes/custom/triple_g');
const style = process.env.NODE_ENV === 'production' || process.argv.includes('--compressed')
  ? 'compressed'
  : 'expanded';
let compiledCount = 0;

async function compileScss(sourcePath, outputPath) {
  const { css } = sass.compile(sourcePath, { sourceMap: false, style });
  await writeFile(outputPath, css);
  compiledCount += 1;
}

async function compileComponentDirectory(directoryPath) {
  const entries = await readdir(directoryPath, { withFileTypes: true });

  for (const entry of entries) {
    const entryPath = path.join(directoryPath, entry.name);
    if (entry.isDirectory()) {
      await compileComponentDirectory(entryPath);
    }
    else if (entry.isFile() && entry.name.endsWith('.scss') && !entry.name.startsWith('_')) {
      await compileScss(entryPath, entryPath.replace(/\.scss$/, '.css'));
    }
  }
}

await compileScss(
  path.join(themeRoot, 'src/scss/theme-tokens.scss'),
  path.join(themeRoot, 'css/theme-tokens.css'),
);
await compileComponentDirectory(path.join(themeRoot, 'components'));

console.log(`Compiled ${compiledCount} Sass files.`);