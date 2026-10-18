import en from '../resources/js/lang/en.json' with { type: 'json' };
import ms from '../resources/js/lang/ms.json' with { type: 'json' };

function flatten(obj, prefix = '') {
    return Object.entries(obj).flatMap(([key, value]) => {
        const path = prefix ? `${prefix}.${key}` : key;
        return typeof value === 'object' && value !== null
            ? flatten(value, path)
            : [path];
    });
}

const enKeys = new Set(flatten(en));
const msKeys = new Set(flatten(ms));

const missingInMs = [...enKeys].filter((key) => !msKeys.has(key));
const missingInEn = [...msKeys].filter((key) => !enKeys.has(key));

if (missingInMs.length || missingInEn.length) {
    if (missingInMs.length) {
        console.error('Missing in ms.json:', missingInMs.join(', '));
    }
    if (missingInEn.length) {
        console.error('Missing in en.json:', missingInEn.join(', '));
    }
    process.exit(1);
}

console.log(`i18n check passed: ${enKeys.size} keys in sync across en/ms.`);
