import { existsSync, unlinkSync } from 'fs';
import { join } from 'path';

const hotFile = join(process.cwd(), 'public', 'hot');

if (existsSync(hotFile)) {
    unlinkSync(hotFile);
    console.log('Removed public/hot file');
} else {
    console.log('No public/hot file found');
}



