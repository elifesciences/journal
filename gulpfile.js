'use strict';

const del = require('del');
const favicons = require('gulp-favicons');
const gulp = require('gulp');
const imageMin = require('gulp-imagemin');
const imageMinMozjpeg = require('imagemin-mozjpeg');
const imageMinOptipng = require('imagemin-optipng');
const merge = require('merge-stream');
const responsive = require('gulp-responsive');

gulp.task('default', ['favicons', 'images']);

gulp.task('favicons:clean', () => {
    return del(['./web/favicons/**/*']);
});

gulp.task('favicons', ['favicons:clean'], () => {
    return gulp.src('./app/Resources/images/source/favicon.svg')
        .pipe(favicons({
            appName: 'eLife',
            appDescription: 'eLife is an open-access journal that publishes research in the life and biomedical sciences',
            background: '#ffffff',
            dir: 'ltr',
            lang: 'en',
            path: '/favicons/',
            url: '/favicons/',
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
        .pipe(gulp.dest('./web/favicons'))
        .pipe(imageMin());
});

gulp.task('images:clean', () => {
    return del(['./app/Resources/images/generated/**/*']);
});

gulp.task('images', ['images:clean'], () => {
    return merge(
        gulp.src('./app/Resources/images/source/*/*.{jpg,png,svg}')
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
        gulp.src('./app/Resources/images/source/*/*.svg')
    )
        .pipe(gulp.dest('./app/Resources/images/generated'));
});
