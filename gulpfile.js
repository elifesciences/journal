'use strict';

const del = require('del');
const favicons = require('gulp-favicons');
const gulp = require('gulp');
const imageMin = require('gulp-imagemin');
const imageMinMozjpeg = require('imagemin-mozjpeg');
const imageMinOptipng = require('imagemin-optipng');
const merge = require('merge-stream');
const responsive = require('gulp-responsive');
const rev = require('gulp-rev-all');

gulp.task('default', ['assets']);

gulp.task('favicons:clean', () => {
    return del(['./build/assets/favicons/**/*']);
});

gulp.task('favicons:build', ['favicons:clean'], () => {
    return gulp.src('./app/Resources/images/favicon.svg')
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
        .pipe(gulp.dest('./build/assets/favicons'))
        .pipe(imageMin());
});

gulp.task('favicons', ['favicons:build'], () => {
    return gulp.src('./build/assets/favicons/favicon.ico')
        .pipe(gulp.dest('./web'));
});

gulp.task('images:clean', () => {
    return del(['./build/assets/images/**/*']);
});

gulp.task('images', ['images:clean'], () => {
    return merge(
        gulp.src('./app/Resources/images/*/*.{jpg,png,svg}')
            .pipe(responsive({
                'banners/**/*': [
                    {
                        width: 1900,
                        height: 800,
                        rename: {
                            suffix: '-hi-res',
                        }
                    }, {
                        width: 950,
                        height: 400,
                        rename: {
                            suffix: '-lo-res',
                        }
                    }
                ],
                'logos/**/*': [
                    {
                        width: 500,
                        rename: {
                            suffix: '-hi-res',
                            extname: '.webp',
                        },
                        withoutEnlargement: false
                    },
                    {
                        width: 250,
                        rename: {
                            suffix: '-lo-res',
                            extname: '.webp',
                        },
                        withoutEnlargement: false
                    },
                    {
                        width: 500,
                        rename: {
                            suffix: '-hi-res',
                            extname: '.png',
                        },
                        withoutEnlargement: false
                    },
                    {
                        width: 250,
                        rename: {
                            suffix: '-lo-res',
                            extname: '.png',
                        },
                        withoutEnlargement: false
                    }
                ]
            }))
            .pipe(imageMin([
                imageMinMozjpeg({
                    quality: 75,
                    progressive: true,
                }),
                imageMinOptipng({})
            ])),
        gulp.src('./app/Resources/images/*/*.svg')
    )
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
            replaceInExtensions: ['.css', '.js', '.json']
        }))
        .pipe(gulp.dest('./web'))
        .pipe(rev.manifestFile())
        .pipe(gulp.dest('./build'));
});
