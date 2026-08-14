#!/usr/bin/env node

'use strict';

var fs = require('fs');
var path = require('path');
var sass = require('sass');

function ensureDir(dirPath) {
    if (!fs.existsSync(dirPath)) {
        fs.mkdirSync(dirPath, { recursive: true });
    }
}

function parseArgs(argv) {
    var mode = 'dev';
    var i;

    for (i = 2; i < argv.length; i++) {
        if (argv[i] === 'dev' || argv[i] === 'prod') {
            mode = argv[i];
        }
    }

    return { mode: mode };
}

function removeFile(filePath) {
    if (fs.existsSync(filePath)) {
        fs.unlinkSync(filePath);
    }
}

function buildCss(mode) {
    var isProd = mode === 'prod';
    var sourceFile = path.join('src', 'frontend', 'rrze-legal.scss');
    var outFile = path.join('build', 'rrze-legal.css');
    var result = sass.compile(sourceFile, {
        style: isProd ? 'compressed' : 'expanded',
        sourceMap: isProd ? false : true,
        sourceMapIncludeSources: true
    });

    fs.writeFileSync(outFile, result.css);

    if (isProd) {
        removeFile(outFile + '.map');
        return true;
    }

    if (result.sourceMap) {
        fs.writeFileSync(outFile + '.map', JSON.stringify(result.sourceMap));
    }

    return true;
}

function runOnce(mode) {
    ensureDir('build');
    buildCss(mode);
    return true;
}

function main() {
    var args = parseArgs(process.argv);
    runOnce(args.mode);
}

main();
