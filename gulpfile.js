'use strict';

const del = require('del');
const gulp = require('gulp');
const imageMin = require('gulp-imagemin');
const imageMinMozjpeg = require('imagemin-mozjpeg');
const responsive = require('gulp-responsive');

gulp.task('default', ['images']);

gulp.task('images:clean', () => {
    del(['./app/Resources/images/generated/**/*']);
});

gulp.task('images', ['images:clean'], () => {
    gulp.src('./app/Resources/images/source/**/*.jpg')
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
            ]
        }))
        .pipe(imageMin([
            imageMinMozjpeg({
                quality: 75,
                progressive: true,
            }),
        ]))
        .pipe(gulp.dest('./app/Resources/images/generated'));
});
