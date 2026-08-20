#!/usr/bin/env node

'use strict';

var fs = require('fs');
var path = require('path');
var sass = require('sass');
var esbuild = require('esbuild');

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

function compileSass(sourceFile, outFile, isProd) {
    var result = sass.compile(sourceFile, {
        style: isProd ? 'compressed' : 'expanded',
        sourceMap: isProd ? false : true,
        sourceMapIncludeSources: true
    });

    fs.writeFileSync(outFile, result.css);

    if (isProd) {
        removeFile(outFile + '.map');
        return;
    }

    if (result.sourceMap) {
        fs.writeFileSync(outFile + '.map', JSON.stringify(result.sourceMap));
    }
}

function removeStaleCssFiles() {
    var staleFiles = [
        path.join('build', 'banner.css'),
        path.join('build', 'banner-rtl.css'),
        path.join('build', 'banner.css.map'),
        path.join('build', 'banner-rtl.css.map'),
        path.join('build', 'settings.css'),
        path.join('build', 'settings-rtl.css'),
        path.join('build', 'settings.css.map'),
        path.join('build', 'settings-rtl.css.map')
    ];
    var i;

    for (i = 0; i < staleFiles.length; i++) {
        removeFile(staleFiles[i]);
    }
}

function phpString(value) {
    return "'" + value.replace(/\\/g, '\\\\').replace(/'/g, "\\'") + "'";
}

function writeInlineScriptAsset(outFile, code) {
    fs.writeFileSync(outFile, "<?php\n\nreturn " + phpString(code) + ";\n");
}

function compileJs(entryFile, outFile, isProd) {
    esbuild.buildSync({
        entryPoints: [entryFile],
        bundle: true,
        minify: isProd,
        sourcemap: isProd ? false : true,
        outfile: outFile,
        target: ['es2018']
    });

    if (isProd) {
        removeFile(outFile + '.map');
    }
}

function compileInlineJs(sourceFile, outFile, isProd) {
    var source = fs.readFileSync(sourceFile, 'utf8');
    var result = esbuild.transformSync(source, {
        minify: isProd,
        sourcemap: false,
        target: 'es2018'
    });

    writeInlineScriptAsset(outFile, result.code);
}

function removeStaleJsFiles() {
    var staleFiles = [
        path.join('build', 'settings.js'),
        path.join('build', 'settings.js.map'),
        path.join('build', 'settings.asset.php'),
        path.join('build', 'tos.js'),
        path.join('build', 'tos.js.map'),
        path.join('build', 'tos.asset.php'),
        path.join('build', 'prioritize.js'),
        path.join('build', 'prioritize.js.map'),
        path.join('build', 'prioritize.asset.php'),
        path.join('build', 'rrze-legal.asset.php')
    ];
    var i;

    for (i = 0; i < staleFiles.length; i++) {
        removeFile(staleFiles[i]);
    }
}

function buildCss(mode) {
    var isProd = mode === 'prod';

    compileSass(
        path.join('src', 'sass', 'rrze-legal.scss'),
        path.join('build', 'rrze-legal.css'),
        isProd
    );
    compileSass(
        path.join('src', 'sass', 'rrze-legal-admin.scss'),
        path.join('build', 'rrze-legal-admin.css'),
        isProd
    );
    removeStaleCssFiles();

    return true;
}

function buildJs(mode) {
    var isProd = mode === 'prod';

    compileJs(
        path.join('src', 'javascript', 'frontend', 'rrze-legal.js'),
        path.join('build', 'rrze-legal.js'),
        isProd
    );
    compileJs(
        path.join('src', 'javascript', 'admin', 'rrze-legal-admin.js'),
        path.join('build', 'rrze-legal-admin.js'),
        isProd
    );
    compileInlineJs(
        path.join('src', 'javascript', 'frontend', 'prioritize.js'),
        path.join('build', 'rrze-legal-prioritize-inline.php'),
        isProd
    );
    removeStaleJsFiles();

    return true;
}

function runOnce(mode) {
    ensureDir('build');
    buildCss(mode);
    buildJs(mode);
    return true;
}

function main() {
    var args = parseArgs(process.argv);
    runOnce(args.mode);
}

main();
