'use strict';

const gulpExec = require('gulp-exec');
const exec = require('child_process').exec;
const execFile = require('child_process').execFile;
const spawn = require('child_process').spawn;

const child = require('child_process');
// const critical = require('critical').stream;
const critical = require('critical');
const del = require('del');
const favicons = require('gulp-favicons');
const gulp = require('gulp');
const gutil = require('gulp-util');
const imageMin = require('gulp-imagemin');
const imageMinMozjpeg = require('imagemin-mozjpeg');
const imageMinOptipng = require('imagemin-optipng');
const imageMinSvgo = require('imagemin-svgo');
const merge = require('merge-stream');
const remoteSrc = require('gulp-remote-src');
const responsive = require('gulp-responsive');
const rev = require('gulp-rev-all');
const runSequence =require('run-sequence');
const svg2png = require('gulp-svg2png');

const criticalConf = {
  cssRuleInclusions: (function () {

    const globalExplicitInclusions = [
      /.*main-menu--js.*/,
      // /.*--js.*/,
      'p',
      /\.content-header.*/,
      /\.meta.*/,
      '.wrapper.wrapper--content',
    ];

    const listingExplicitInclusions = [/\.teaser__img--.*$/];
    const highlightsExplicitInclusion = [/.*\.highlights.*$/];

    return {
      article: globalExplicitInclusions.concat(
        [
          /\.content-header__item_toggle--.*$/,
          '.view-selector__list-item--side-by-side'
        ]
      ),
      archiveMonth: globalExplicitInclusions.concat(
        highlightsExplicitInclusion,
        /\.teaser.*$/
      ),
      home: globalExplicitInclusions.concat(
        [
          listingExplicitInclusions,
          /^.*carousel.*$/,
        ]
      ),
      landing: globalExplicitInclusions.concat(
        [
          listingExplicitInclusions,
          /.content-header.wrapper.*/,
          '.section-listing-link'
        ]
      ),
      magazine: globalExplicitInclusions.concat(
        [
          /^\.audio-player.*$/,
          highlightsExplicitInclusion,
          listingExplicitInclusions
        ]
      ),
      listing: globalExplicitInclusions.concat(listingExplicitInclusions),
      gridListing: globalExplicitInclusions.concat(
        [
          /^.*\.grid-listing.*$/,
          '.teaser__header_text',
          '.teaser__header_text_link'
        ]
      ),
      default: globalExplicitInclusions.concat(
        [
          '.article-section__header_text',
          '.list--bullet a'
        ]
      )
    };

  }()),
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
  // TODO: Switch server back to localhost
  // serverAddress: 'elifesciences.org'
  serverAddress: '127.0.0.1:8089'
};

criticalConf.srcPrefix = criticalConf.serverAddress.indexOf('127') !== 0 ? 'https://' : 'http://';
// criticalConf.baseUrl = `${criticalConf.srcPrefix}${criticalConf.serverAddress}/`;
criticalConf.baseUrl = `${criticalConf.srcPrefix}${criticalConf.serverAddress}/`;

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

gulp.task('launch-ls',function(done) {
  child.spawn('ls', [ '-la'], { stdio: 'inherit' });
});


function generateCriticalCss(page, callback) {
  gutil.log(gutil.colors.blue(`${criticalConf.baseUrl}${page.url}`));

  // return critical.generate({
  //                     inline: false,
  //                     base: './app/Resources/views/critical',
  //                     dest: `critical-css-inline-${page.name}.css.twig`,
  //                     src: `${criticalConf.baseUrl}${page.url}`,
  //                     include: page.explicitInclusions,
  //                     minify: true,
  //                     timeout: 90000,
  //                     dimensions: criticalConf.dimensions
  //                   })
  //         .then(() => { callback(); })
  //         .error((err) => {
  //           gutil.log(gutil.colors.red(err));
  //         });


  return remoteSrc([page.url], { base: criticalConf.baseUrl })
    .pipe(critical.stream(
      {
        inline: false,
        // base: 'app/Resources/views/critical/',
        base: 'web',
        dest: `../app/Resources/views/critical/critical-css-inline-${page.name}.css.twig`,
        // destFolder: '../app/Resources/views/critical',
        src: page.url,
        include: page.explicitInclusions,
        minify: true,
        dimensions: criticalConf.dimensions
      })).on('exit', function(err) { gutil.log(gutil.colors.red(err.message)); })
}

gulp.task('generateCriticalCss:article',  (callback) => {
  const page = {
      name: 'article',
      url: 'articles/09560',
      explicitInclusions: criticalConf.cssRuleInclusions.article
    };

  // return generateCriticalCss(page);
  gutil.log(gutil.colors.blue(`${criticalConf.baseUrl}${page.url}`));


  critical.generate({
                      inline: false,
                      base: './app/Resources/views/critical',
                      dest: `critical-css-inline-${page.name}.css.twig`,
                      src: `${criticalConf.baseUrl}${page.url}`,
                      include: page.explicitInclusions,
                      minify: true,
                      timeout: 90000,
                      dimensions: criticalConf.dimensions
                    })
    .then(() => { callback(); })
    .error((err) => {
      gutil.log(gutil.colors.red(err));
    });


  // return remoteSrc([page.url], { base: criticalConf.baseUrl })
  //   .pipe(critical.stream(
  //     {
  //       inline: false,
  //       // base: 'app/Resources/views/critical/',
  //       base: 'web',
  //       dest: `../app/Resources/views/critical/critical-css-inline-${page.name}.css.twig`,
  //       // dest: `critical-css-inline-${page.name}.css.twig`,
  //       // destFolder: '../app/Resources/views/critical',
  //       // src: page.url,
  //       include: page.explicitInclusions,
  //       minify: true,
  //       timeout: 120000,
  //       dimensions: criticalConf.dimensions
  //     }))
  //   .on('exit', function(err) { gutil.log(gutil.colors.red(err.message)); })
  //   // .pipe(gulp.dest('app/Resources/views/critical'));
});

gulp.task('generateCriticalCss:archiveMonth',  () => {
  const page = {
    name: 'archive-month',
    url: 'archive/2017/january',
    explicitInclusions: criticalConf.cssRuleInclusions.archiveMonth
  };

  return generateCriticalCss(page);
});

gulp.task('generateCriticalCss:landing',  () => {
  const page = {
    name: 'landing',
    url: 'subjects/biochemistry',
    explicitInclusions: criticalConf.cssRuleInclusions.landing
  };

  return generateCriticalCss(page);
});

gulp.task('generateCriticalCss:magazine',  () => {
  const page = {
    name: 'magazine',
    url: '/magazine',
    explicitInclusions: criticalConf.cssRuleInclusions.magazine
  };

  return generateCriticalCss(page);
});

gulp.task('generateCriticalCss:listing',  () => {
  const page = {
    name: 'listing',
    url: '/?page=2',
    explicitInclusions: criticalConf.cssRuleInclusions.listing
  };

  return generateCriticalCss(page);
});

gulp.task('generateCriticalCss:gridListing',  () => {
  const page = {
    name: 'grid-listing',
    url: '/archive/2017',
    explicitInclusions: criticalConf.cssRuleInclusions.gridListing
  };

  return generateCriticalCss(page);
});

gulp.task('generateCriticalCss:default',  () => {
  const page = {
    name: 'default',
    url: '/about/people',
    explicitInclusions: criticalConf.cssRuleInclusions.default
  };

  return generateCriticalCss(page);
});

gulp.task('generateCriticalCss', (callback) => {
  runSequence('server:start', ['generateCriticalCss:article'/*, 'generateCriticalCss:archiveMonth', 'generateCriticalCss:landing', 'generateCriticalCss:magazine', 'generateCriticalCss:listing', 'generateCriticalCss:gridListing', 'generateCriticalCss:default'*/], 'server:stop', callback);
});

  gulp.task('generateCriticalCss:unchained', [/*'server:start'*/], (cb) => {

  const pagesToAnalyse = [
    {
      name: 'article',
      url: '/articles/09560',
      explicitInclusions: criticalConf.cssRuleInclusions.article
    },
    // {
    //   name: 'archive-month',
    //   url: '/archive/2017/january',
    //   explicitInclusions: criticalConf.cssRuleInclusions.archiveMonth
    // },
    // {
    //   name: 'home',
    //   url: '/',
    //   explicitInclusions: criticalConf.cssRuleInclusions.home
    // },
    // {
    //   name: 'landing',
    //   url: '/subjects/biochemistry',
    //   explicitInclusions: criticalConf.cssRuleInclusions.landing
    // },
    // {
    //   name: 'magazine',
    //   url: '/magazine',
    //   explicitInclusions: criticalConf.cssRuleInclusions.magazine
    // },
    // {
    //   name: 'listing',
    //   url: '/?page=2',
    //   explicitInclusions: criticalConf.cssRuleInclusions.listing
    // },
    // {
    //   name: 'grid-listing',
    //   url: '/archive/2017',
    //   explicitInclusions: criticalConf.cssRuleInclusions.gridListing
    // },
    // {
    //   name: 'default',
    //   url: '/about/people',
    //   explicitInclusions: criticalConf.cssRuleInclusions.default
    // },
  ];

  pagesToAnalyse.forEach((page) => {
    // const srcPrefix = criticalConf.serverAddress.indexOf('http') !== 0 ? 'https://' : '';
    // return gulp.src('**/**')
    //            .pipe(startLocalServer)
    //            .pipe(critical(
    critical.generate({
                        inline: false,
                        base: 'app/Resources/views/critical',
                        dest: `critical-css-inline-${page.name}.css.twig`,
                        src: `${criticalConf.srcPrefix}${criticalConf.serverAddress}${page.url}`,
                        include: page.explicitInclusions,
                        minify: true,
                        timeout: 90000,
                        dimensions: criticalConf.dimensions
                      }
                      // ).then(stopLocalServer,stopLocalServer);
    );//).on('error', (err) => { console.log(`Hello!\n${err}`)});
  });
});

gulp.task('server:start', (callback) => {
  const command = spawn('bin/console', ['server:start', criticalConf.serverAddress]);

  command.stdout.on('data', (data) => {
    console.log(`stdout: ${data}`);
  });

  command.stderr.on('data', (data) => {
    console.log(`stderr: ${data}`);
  });

  command.on('exit', (code) => {
    callback(code);
  });
});

gulp.task('server:stop', (callback) => {
  const command = spawn('bin/console', ['server:stop', criticalConf.serverAddress]);

  command.stdout.on('data', (data) => {
    console.log(`stdout: ${data}`);
  });

  command.stderr.on('data', (data) => {
    console.log(`stderr: ${data}`);
  });

  command.on('exit', (code) => {
    callback(code);
  });
});
