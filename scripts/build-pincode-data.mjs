// Rebuild the bundled lookup from a pinned, MIT-licensed source snapshot.
import { mkdir, writeFile } from 'node:fs/promises';
const commit = '105d24c80867c8a7121c140add8792165d0b7df5';
const base = `https://raw.githubusercontent.com/bilal-webdev/india-postal-pincode-dataset/${commit}/`;
async function download(path) {
    const response = await fetch(base + path);
    if (!response.ok) throw new Error(`Download failed: ${response.status}`);
    return response.text();
}
const source = JSON.parse(await download('json/india-postal-by-pincode.json'));
const names = [];
const pins = {};
let ambiguous = 0;
const title = value => value.toLowerCase().replace(/\b\w/g, letter => letter.toUpperCase()).trim();
for (const [pin, entry] of Object.entries(source)) {
    if (!/^[1-9][0-9]{5}$/.test(pin)) continue;
    const districts = [...new Set(entry.districts.map(row => row.district.trim()))];
    if (districts.length !== 1 || !districts[0] || !entry.state) { ambiguous++; continue; }
    const location = [title(districts[0]), title(entry.state)];
    let index = names.findIndex(row => row[0] === location[0] && row[1] === location[1]);
    if (index === -1) { index = names.length; names.push(location); }
    pins[pin] = index;
}
await mkdir('public/data', { recursive: true });
await writeFile('public/data/pincodes.json', JSON.stringify({version: commit, locations: names, pins}) + '\n');
await writeFile('public/data/PINCODE-LICENSE.txt', await download('LICENSE'));
await writeFile('public/data/PINCODE-SOURCE.md', `# PIN code lookup data\n\nSource: https://github.com/bilal-webdev/india-postal-pincode-dataset\n\nSnapshot: ${commit}\n\nLicense: MIT (see PINCODE-LICENSE.txt).\n\nRebuild: node scripts/build-pincode-data.mjs\n\n${Object.keys(pins).length} unambiguous PIN codes, ${names.length} district/state pairs. ${ambiguous} ambiguous records are excluded and use the verified API fallback; no district is guessed from a PIN prefix.\n`);
console.log(`${Object.keys(pins).length} PIN codes; ${ambiguous} ambiguous records omitted.`);
