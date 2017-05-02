'use strict';

const del = require('del');
const favicons = require('gulp-favicons');
const gulp = require('gulp');
const imageMin = require('gulp-imagemin');
const imageMinMozjpeg = require('imagemin-mozjpeg');
const imageMinSvgo = require('imagemin-svgo');
const imageMinWebp = require('imagemin-webp');
const imageMinZopfli = require('imagemin-zopfli');
const merge = require('merge-stream');
const responsive = require('gulp-responsive');
const rev = require('gulp-rev-all');
const svg2png = require('gulp-svg2png');

gulp.task('default', ['assets']);

gulp.task('favicons:clean', () => {
    return del(['./build/assets/favicons/**/*']);
});

gulp.task('favicons:build', ['favicons:clean'], () => {
    return gulp.src('./app/Resources/images/favicon.svg')
        .pipe(svg2png({width: 512, height: 512}))
        .pipe(favicons({
            appName: 'eLife',
            appDescription: 'eLife is an open-access journal that publishes research in the life and biomedical sciences',
            background: '#ffffff',
            dir: 'ltr',
            lang: 'en',
            path: '/assets/favicons/',
            url: '/assets/favicons/',
            display: 'browser',
            start_url: '/',
            icons: {
                appleStartup: false,
                coast: false,
                firefox: false,
                windows: false,
                yandex: false,
            },
        }))
        .pipe(imageMin([
            imageMinZopfli({
                more: true,
            }),
        ]))
        .pipe(gulp.dest('./build/assets/favicons'));
});

gulp.task('favicons', ['favicons:build'], () => {
    return gulp.src('./build/assets/favicons/favicon.ico')
        .pipe(gulp.dest('./web'));
});

gulp.task('images:clean', () => {
    return del(['./build/assets/images/**/*']);
});

gulp.task('images:banners', ['images:clean'], () => {
    const sizes = {2228: 672, 1114: 336, 2046: 576, 1023: 288, 1534: 528, 767: 264, 900: 528, 450: 264};

    return gulp.src('./app/Resources/images/banners/*.jpg')
        .pipe(responsive({
            '*': Object.keys(sizes).reduce((acc, width) => {
                let height = sizes[width];

                acc.push({
                    width: width,
                    height: height,
                    rename: {
                        suffix: `-${width}x${height}`,
                    },
                    withoutEnlargement: false,
                });

                return acc;
            }, []),
        }))
        .pipe(gulp.dest('./build/assets/images/banners'));
});

gulp.task('images:logos', ['images:clean'], () => {
    return gulp.src('./app/Resources/images/logos/*.{png,svg}')
        .pipe(responsive({
            '*': [250, 500].reduce((acc, width) => {
                ['webp', 'png'].reduce((acc, format) => {
                    acc.push({
                        width: width,
                        rename: {
                            suffix: `-${width}`,
                            extname: `.${format}`,
                        },
                        withoutEnlargement: false,
                    });

                    return acc;
                }, acc);

                return acc;
            }, []),
        }))
        .pipe(gulp.dest('./build/assets/images/logos'));
});

gulp.task('images:svgs', ['images:clean'], () => {
    return gulp.src('./app/Resources/images/*/*.svg')
        .pipe(gulp.dest('./build/assets/images'));
});

gulp.task('images', ['images:banners', 'images:logos', 'images:svgs'], () => {
    return gulp.src('./build/assets/images/**/*')
        .pipe(imageMin([
            imageMinMozjpeg({
                quality: 75,
                progressive: true,
            }),
            imageMinSvgo({}),
            imageMinWebp({
                quality: 65,
            }),
            imageMinZopfli({
                more: true,
            }),
        ]))
        .pipe(gulp.dest('./build/assets/images'));
});

gulp.task('patterns:clean', () => {
    return del(['./build/assets/patterns/**/*']);
});

gulp.task('patterns', () => {
    return gulp.src('./vendor/elife/patterns/resources/assets/**/*')
        .pipe(gulp.dest('./build/assets/patterns'));
});

gulp.task('assets:clean', () => {
    return del(['./web/assets/**/*']);
});

gulp.task('assets', ['assets:clean', 'favicons', 'images', 'patterns'], () => {
    return gulp.src('./build/assets/**/*.*', {base: "./build", follow: true})
        .pipe(rev.revision({
            includeFilesInManifest: ['.css', '.jpg', '.js', '.json', '.ico', '.png', '.svg', '.webp', '.woff', '.woff2'],
            replaceInExtensions: ['.css', '.js', '.json'],
        }))
        .pipe(gulp.dest('./web'))
        .pipe(rev.manifestFile())
        .pipe(gulp.dest('./build'));
});
