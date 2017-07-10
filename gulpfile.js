'use strict';

const critical = require('critical');
const del = require('del');
const favicons = require('gulp-favicons');
const fs = require('fs');
const gulp = require('gulp');
const gutil = require('gulp-util');
const imageMin = require('gulp-imagemin');
const imageMinMozjpeg = require('imagemin-mozjpeg');
const imageMinOptipng = require('imagemin-optipng');
const imageMinSvgo = require('imagemin-svgo');
const merge = require('merge-stream');
const responsive = require('gulp-responsive');
const rev = require('gulp-rev-all');
const runSequence =require('run-sequence');
const spawn = require('child_process').spawn;
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
            imageMinOptipng({
                optimizationLevel: 4,
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
                acc.push({
                    width: width,
                    rename: {
                        suffix: `-${width}`,
                        extname: '.png',
                    },
                    withoutEnlargement: false,
                });

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
            includeFilesInManifest: ['.css', '.jpg', '.js', '.json', '.ico', '.png', '.svg', '.woff', '.woff2'],
            replaceInExtensions: ['.css', '.js', '.json'],
        }))
        .pipe(gulp.dest('./web'))
        .pipe(rev.manifestFile())
        .pipe(gulp.dest('./build'));
});


const criticalCssConfig = (function() {

    const explicitlyIncludedSelectors = (function () {
        const global = [
            /.*\.main-menu(--js)?.*/,
            'p',
            /\.content-header.*/,
            /\.meta.*/,
            '.wrapper.wrapper--content',
        ];
        const listing = [/\.teaser__img--.*$/];
        const highlights = [/.*\.highlights.*$/];
        const listingMenu = [
            '.section-listing-wrapper .list-heading',
            '.section-listing__list_item',
            /.*\.section-listing.*/,
            '.js .to-top-link',
        ];

        return {
            article: global.concat(
                /\.content-header__item_toggle--.*$/,
                '.view-selector__list-item--side-by-side',
                '.see-more-link'
            ),

            "archive-month": global.concat(
                highlights,
                /\.teaser.*$/
            ),

            home: global.concat(
                listing,
                listingMenu,
                /.*\.carousel.*/,
                '.carousel__control--toggler',
                '.carousel__items',
                /\.carousel__item.*/
            ),

            landing: global.concat(
                listing,
                /.content-header.wrapper.*/,
                '.section-listing-link'
            ),

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
                '.teaser__header_text',
                '.teaser__header_text_link'
            ),

            default: global.concat(
                '.article-section__header_text',
                '.list--bullet a'
            )
        };
    }());

    const serverAddress = 'localhost';
    const port = '8089';

    return {
        serverAddress: serverAddress,
        port: port,
        baseUrl: `http://${serverAddress}:${port}`,
        baseFilePath: './app/Resources/views/critical',
        dimensions: [
            {
                height: 400,
                width: 729
            },
            {
                height: 1000,
                width: 899
            },
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


gulp.task('generateCriticalCss', ['server:start'], (callback) => {

    fs.stat(criticalCssConfig.baseFilePath, function(err) {
        if(err) {
            fs.mkdirSync(criticalCssConfig.baseFilePath);
        }
    });

    runSequence('generateCriticalCss:article', 'generateCriticalCss:archiveMonth', 'generateCriticalCss:gridListing', 'generateCriticalCss:home', 'generateCriticalCss:landing', 'generateCriticalCss:listing', 'generateCriticalCss:magazine', 'generateCriticalCss:people', (err) => {
        if (err) {
            gutil.log(gutil.colors.red(`generateCriticalCss task failed with ${err}`));
            changeServerState('stop');
            return process.exit(1);
        } else {
            return changeServerState('stop', callback);
        }
    });

});

function generateCriticalCss(page, callback) {

    gutil.log(gutil.colors.blue(`Generating critical CSS for ${page.name} page based on ${criticalCssConfig.baseUrl}${page.url}`));

    return critical.generate({
        inline: false,
        base: criticalCssConfig.baseFilePath,
        dest: `critical-css-inline-${page.name}.css.twig`,
        src: `${criticalCssConfig.baseUrl}${page.url}`,
        include: criticalCssConfig.getInclusions(page.name),
        minify: true,
        dimensions: criticalCssConfig.dimensions,
        timeout: 90000
    })
    .then(() => {
        return callback();
    }, (err) => {
        gutil.log(gutil.colors.red(`Generating critical CSS failed with ${err}`));
        changeServerState('stop');
        return process.exit(1);
    });
}

gulp.task('generateCriticalCss:article',  (callback) => {
    generateCriticalCss({ name: 'article',  url: '/articles/00569'}, callback);
});

gulp.task('generateCriticalCss:archiveMonth',  (callback) => {
    generateCriticalCss({ name: 'archive-month', url: '/archive/2016/march' }, callback);
});

gulp.task('generateCriticalCss:landing',  (callback) => {
    // TODO: populate right hand column
    generateCriticalCss({ name: 'landing', url: '/subjects/biochemistry' }, callback);
});

gulp.task('generateCriticalCss:home',  (callback) => {
    // TODO: carousel
    generateCriticalCss({ name: 'home', url: '/' }, callback);
});

gulp.task('generateCriticalCss:magazine',  (callback) => {
    // TODO: add in highlights carousel
    generateCriticalCss({ name: 'magazine', url: '/magazine' }, callback);
});

gulp.task('generateCriticalCss:listing',  (callback) => {
    generateCriticalCss({ name: 'listing', url: '/?page=2' }, callback);
});

gulp.task('generateCriticalCss:gridListing',  (callback) => {
    generateCriticalCss({ name: 'grid-listing', url: '/archive/2016' }, callback);
});

gulp.task('generateCriticalCss:people',  (callback) => {
    generateCriticalCss({ name: 'people', url: '/about/people' }, callback);
});

gulp.task('server:start', (callback) => {
    changeServerState('start', callback);
});

function changeServerState(serverAction, callback) {
    const action = serverAction === 'start' ? 'start' : 'stop';

    const command = spawn('bin/console', [`server:${action}`, `${criticalCssConfig.serverAddress}:${criticalCssConfig.port}`]);

    command.stdout.on('data', (data) => {
        console.log(`stdout: ${data}`);
    });

    command.stderr.on('data', (data) => {
        console.log(`stderr: ${data}`);
    });

    command.on('exit', (code) => {
        let suffix = 'ed';
        suffix = action === 'stop' ? `p${suffix}` : suffix;
        gutil.log(gutil.colors.blue(`${action}${suffix} server on ${criticalCssConfig.serverAddress}:${criticalCssConfig.port}`));
        if (callback && typeof callback === 'function') {
            return callback(code);
        }
    });

}
