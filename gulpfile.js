'use strict';

const critical = require('critical');
const del = require('del');
const eachOfLimit = require('async/eachOfLimit');
const favicons = require('gulp-favicons');
const gulp = require('gulp');
const imageMin = require('gulp-imagemin');
const imageMinMozjpeg = require('imagemin-mozjpeg');
const imageMinOptipng = require('imagemin-optipng');
const imageMinSvgo = require('imagemin-svgo');
const merge = require('merge-stream');
const responsive = require('gulp-responsive');
const request = require('request');
const rev = require('gulp-rev-all');

let criticalCssPageTypes = {};
try {
    criticalCssPageTypes = require('./critical-css.json');
} catch (exception) {
    // Do nothing.
}

gulp.task('default', ['assets']);

gulp.task('favicons:clean', () => {
    return del(['./build/assets/favicons/**/*']);
});

gulp.task('favicons:build', ['favicons:clean'], () => {
    return gulp.src('./assets/images/favicon.svg')
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
            imageMinOptipng({
                optimizationLevel: 4,
            }),
        ]))
        .pipe(gulp.dest('./build/assets/favicons'));
});

gulp.task('favicons:svg', ['favicons:clean'], () => {
    return gulp.src('./assets/images/favicon.svg')
        .pipe(gulp.dest('./build/assets/favicons'));
});

gulp.task('favicons', ['favicons:build', 'favicons:svg'], () => {
    return gulp.src('./build/assets/favicons/favicon.ico')
        .pipe(gulp.dest('./web'));
});

gulp.task('images:clean', () => {
    return del(['./build/assets/images/**/*']);
});

gulp.task('images:banners', ['images:clean'], () => {
    const sizes = {1114: 336, 1023: 336, 899: 288, 729: 264, 450: 264};

    return gulp.src('./assets/images/banners/*.jpg')
        .pipe(responsive({
            '*': [1, 2].reduce((acc, scale) => {
                return Object.keys(sizes).reduce((acc, width) => {
                    const height = sizes[width];

                    acc.push({
                        width: width * scale,
                        height: height * scale,
                        rename: {
                            suffix: `-${width}x${height}@${scale}`,
                        },
                        withoutEnlargement: false,
                    });
                    acc.push({
                        width: width * scale,
                        height: height * scale,
                        quality: 65,
                        rename: {
                            suffix: `-${width}x${height}@${scale}`,
                            extname: '.webp',
                        },
                        withoutEnlargement: false,
                    });

                    return acc;
                }, acc);
            }, []),
        }))
        .pipe(gulp.dest('./build/assets/images/banners'));
});

gulp.task('images:social', ['images:clean'], () => {
    return gulp.src('./assets/images/social/*.png')
        .pipe(responsive({
            '*': [1, 2].reduce((acc, scale) => {
                const width = 280 * scale;
                const height = 200 * scale;

                acc.push({
                    width: width,
                    height: height,
                    rename: {
                        suffix: `-${width}x${height}@${scale}`,
                    },
                    withoutEnlargement: false,
                });
                acc.push({
                    width: width,
                    height: height,
                    rename: {
                        suffix: `-${width}x${height}@${scale}`,
                        extname: '.webp',
                    },
                    withoutEnlargement: false,
                });

                return acc;
        }, []),
        }))
        .pipe(gulp.dest('./build/assets/images/social'));
});

gulp.task('images:logos', ['images:clean'], () => {
    return gulp.src('./assets/images/logos/*.{png,svg}')
        .pipe(responsive({
            '*': [1, 2].reduce((acc, scale) => {
                const width = 180 * scale;
                const height = 60 * scale;

                acc.push({
                    width: width,
                    height: height,
                    max: true,
                    rename: {
                        suffix: `@${scale}x`,
                        extname: '.png',
                    },
                    withoutEnlargement: false,
                });
                acc.push({
                    width: width,
                    height: height,
                    max: true,
                    rename: {
                        suffix: `@${scale}x`,
                        extname: '.webp',
                    },
                    withoutEnlargement: false,
                });

                return acc;
            }, []),
        }))
        .pipe(gulp.dest('./build/assets/images/logos'));
});

gulp.task('images:investors', ['images:clean'], () => {
    return gulp.src('./assets/images/investors/*.{png,svg}')
        .pipe(responsive({
            '*': [1, 2].reduce((acc, scale) => {
                const width = 185 * scale;
                const height = 72 * scale;

                acc.push({
                    width: width,
                    height: height,
                    max: true,
                    rename: {
                        suffix: `@${scale}x`,
                        extname: '.png',
                    },
                    withoutEnlargement: false,
                });
                acc.push({
                    width: width,
                    height: height,
                    max: true,
                    quality: 65,
                    rename: {
                        suffix: `@${scale}x`,
                        extname: '.webp',
                    },
                    withoutEnlargement: false,
                });

                return acc;
            }, []),
        }))
        .pipe(gulp.dest('./build/assets/images/investors'));
});

gulp.task('images:svgs', ['images:clean'], () => {
    return gulp.src('./assets/images/*/*.svg')
        .pipe(gulp.dest('./build/assets/images'));
});

gulp.task('images', ['images:banners', 'images:logos', 'images:social', 'images:investors', 'images:svgs'], () => {
    return gulp.src('./build/assets/images/**/*')
        .pipe(imageMin([
            imageMinMozjpeg({
                quality: 75,
                progressive: true,
            }),
            imageMinOptipng({
                optimizationLevel: 4,
            }),
            imageMinSvgo({}),
        ]))
        .pipe(gulp.dest('./build/assets/images'));
});

gulp.task('patterns:clean', () => {
    return del(['./build/assets/patterns/**/*']);
});

gulp.task('patterns', ['patterns:clean'], () => {
    return gulp.src([
        './vendor/elife/patterns/resources/assets/**/*',
        '!./vendor/elife/patterns/resources/assets/js/elife-loader.js',
        '!./vendor/elife/patterns/resources/assets/preload.json',
    ])
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

gulp.task('critical-css:clean', () => {
    return del([criticalCssConfig.baseFilePath + '/**/*']);
});

gulp.task('critical-css:generate', ['critical-css:clean'], (callback) => {

    eachOfLimit(criticalCssPageTypes, 1, (path, name, callback) => {
        const uri = criticalCssConfig.baseUrl + path;

        request(uri, (error, response, html) => {
            if (error) {
                return callback(error);
            } else if (response.statusCode < 200 || response.statusCode >= 300) {
                return callback(new Error(`Request ${uri} failed with status code ${response.statusCode}`));
            }

            critical.generate({
                inline: false,
                base: `${criticalCssConfig.baseFilePath}`,
                dest: `${name}.css`,
                html: html,
                src: uri,
                include: criticalCssConfig.getInclusions(name),
                pathPrefix: `${criticalCssConfig.assetPathPrefix}/level-to-be-raised-from-by-actual-path-double-dot/`,
                minify: true,
                dimensions: criticalCssConfig.dimensions,
                timeout: 90000
            }, callback)
        });
    }, callback);
});

const criticalCssConfig = (function () {

    const explicitlyIncludedSelectors = (function () {
        const global = [
            /.*\.main-menu(--js)?.*/,
            'p',
            /\.content-header.*/,
            /\.meta.*/,
            '.hidden',
            '.wrapper.wrapper--content',
            /\.contextual-data.*/,
            /\.login-control.*/,
            /\.speech-bubble.*/,
        ];
        const highlights = [/.*\.highlights.*$/];
        const listing = [
            /\.teaser__img--.*$/,
            /.*\.teaser__formats-list.*/
        ];
        const listingMenu = [
            '.section-listing-wrapper .list-heading',
            '.section-listing__list_item',
            /.*\.section-listing.*/,
            '.js .to-top-link',
        ];

        const landing = listing.concat(
            /.content-header.wrapper.*/,
            '.section-listing-link'
        );

        return {
            article: global.concat(
                /\.content-header__item_toggle--.*$/,
                /\.info-bar((--|__).+)?$/,
                /\.view-selector.*/,
                'h2',
                '.article-section__header_text',
                '.article-section--first .article-section__header:first-child h2',
                '.dismiss-button',
                '.doi a.doi__link',
                '.doi--article-section a.doi__link',
                '.doi--article-section',
                '.grid',
                '.grid:before',
                '.grid:after',
                '.grid-column',
                '.grid__item',
                '.grid-secondary-column__item',
                '.large--eight-twelfths',
                '.large--ten-twelfths',
                '.x-large--two-twelfths',
                '.x-large--seven-twelfths',
                '.x-large--eight-twelfths',
                '.push--large--one-twelfth',
                '.push--x-large--two-twelfths',
                '.push--x-large--zero',
                '.see-more-link'
            ),

            about: global.concat(
                landing,
                /.*\.section-listing.*$/,
                /.*.to-top-link$/
            ),

            "archive-month": global.concat(
                highlights,
                /\.teaser.*$/
            ),

            collection: global.concat(listing),

            home: global.concat(
                listing,
                listingMenu
            ),

            landing: global.concat(landing),

            magazine: global.concat(
                listing,
                listingMenu,
                highlights,
                /^\.audio-player.*$/
            ),

            listing: global.concat(listing),

            'grid-listing': global.concat(
                /.*\.grid-listing.*/,
                /.*\.block-link--grid-listing.*/,
                'h4',
                '.teaser__header_text',
                '.teaser__header_text_link'
            ),

            people: global.concat(
                '.article-section__header_text',
                '.list--bullet a'
            ),

            'podcast-episode': global.concat(
                listing,
                /.*\.audio-player.*$/,
                '.media-chapter-listing-item__header_text_link'
            ),

            post: global,

            default: global.concat(
                '.article-section__header_text',
                '.list--bullet a'
            )
        };
    }());

    return {
        baseUrl: process.env.CRITICAL_CSS_BASE_URL || 'http://localhost:8080',
        baseFilePath: './build/critical-css',
        assetPathPrefix: '/assets/patterns',
        dimensions: [
            {
                height: 1000,
                width: 1199
            },
            {
                height: 1000,
                width: 1201
            }
        ],
        getInclusions: (pageName) => {
            return explicitlyIncludedSelectors[pageName];
        }
    };

}());
