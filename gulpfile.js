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
const imageMin = require('gulp-imagemin');
const imageMinMozjpeg = require('imagemin-mozjpeg');
const imageMinOptipng = require('imagemin-optipng');
const imageMinSvgo = require('imagemin-svgo');
const merge = require('merge-stream');
const responsive = require('gulp-responsive');
const rev = require('gulp-rev-all');
const svg2png = require('gulp-svg2png');

// const serverAddress = 'localhost:8089';
// const serverAddress = 'http://localhost:8000';
const serverAddress = 'elifesciences.org';

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

gulp.task('generateCriticalCss', /*['server:start'],*/ (cb) => {

  // child.spawn('bin/console', [ 'server:start', serverAddress], { stdio: 'inherit' });

  //setTimeout(() => {
  const globalExplicitInclusions = [
    /.*main-menu--js.*/,
    // /.*--js.*/,
    'p',
    /\.content-header.*/,
    /\.meta.*/,
    '.wrapper.wrapper--content'
  ];

  const listingExplicitInclusions = [/\.teaser__img--.*$/];
  const highlightsExplicitInclusion = [/.*\.highlights.*$/];

  const explicitInclusions = {
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

  const pagesToAnalyse = [
    {
      name: 'article',
      relativeUrl: '/articles/09560',
      explicitInclusions: explicitInclusions.article
    },
    {
      name: 'archive-month',
      relativeUrl: '/archive/2017/january',
      explicitInclusions: explicitInclusions.archiveMonth
    },
    {
      name: 'home',
      relativeUrl: '/',
      explicitInclusions: explicitInclusions.home
    },
    {
      name: 'landing',
      relativeUrl: '/subjects/biochemistry',
      explicitInclusions: explicitInclusions.landing
    },
    {
      name: 'magazine',
      relativeUrl: '/magazine',
      explicitInclusions: explicitInclusions.magazine
    },
    {
      name: 'listing',
      relativeUrl: '/?page=2',
      explicitInclusions: explicitInclusions.listing
    },
    {
      name: 'grid-listing',
      relativeUrl: '/archive/2017',
      explicitInclusions: explicitInclusions.gridListing
    },
    {
      name: 'default',
      relativeUrl: '/about/people',
      explicitInclusions: explicitInclusions.default
    },
  ];

  // exec(`bin/console server:start ${serverAddress} &`);
  pagesToAnalyse.forEach((page) => {
    // generateCriticalCss(page.name, `http://${serverAddress}${page.relativeUrl}`);
    const srcPrefix = serverAddress.indexOf('http') !== 0 ? 'https://' : '';
    // return gulp.src('**/**')
    //            .pipe(startLocalServer)
    //            .pipe(critical(
    critical.generate({
                        inline: false,
                        base: 'app/Resources/views/critical',
                        // dest: `critical-css-inline-article.css.twig`,
                        dest: `critical-css-inline-${page.name}.css.twig`,
                        // src: `http://${serverAddress}${page.relativeUrl}`,
                        src: `${srcPrefix}${serverAddress}${page.relativeUrl}`,
                        // src: `${srcPrefix}://${serverAddress}/articles/09560`,
                        // src: `https://elifesciences.org${page.relativeUrl}`,
                        include: page.explicitInclusions,
                        minify: true,
                        timeout: 60000,
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
                        ]
                      }
                      // ).then(stopLocalServer,stopLocalServer);
    );//).on('error', (err) => { console.log(`Hello!\n${err}`)});
    // });

    // exec(`bin/console server:stop ${serverAddress} &`);
    // child.spawn('bin/console', [ 'server:stop', serverAddress], { stdio: 'inherit' });
    // cb();


    //}, 1000);
  });
});

gulp.task('critical', ['generateCriticalCss'], () => {
  stopLocalServer();
});

gulp.task('server:start', (cb) => {
  // exec(`bin/console server:start ${serverAddress} &`);
  // child.spawn('bin/console', [ 'server:start', serverAddress], { stdio: 'inherit' });
  // cb();
  // startLocalServer();
  // execFile('node', ['--version'], (error, stdout, stderr) => {
  //   if (error) {
  //     console.error('stderr', stderr);
  //     throw error;
  //   }
  //   console.log('stdout', stdout);
  // });

  // execFile('bin/console', ['server:start', serverAddress], (error, stdout, stderr) => {
  //   if (error) {
  //     console.error('stderr', stderr);
  //     throw error;
  //   }
  //   console.log('stdout', stdout);
  // });

  return gulp.src('app/Resources/views/**/*')
             // .pipe(gulpExec(`bin/console server:start ${serverAddress} &`)).on('start', () => {
             .pipe(gulpExec(`php bin/console server:start ${serverAddress}`)).on('start', () => {
      // .pipe(gulpExec('ls -lah').on('start', () => {
      console.log('\n');
      console.log(`Built in PHP server started on ${serverAddress}`);
      console.log('\n');
      // }));
    });

  // return exec(`bin/console server:start ${serverAddress} & echo`, function (err, stdout, stderr) {
  //   console.log(err);
  //   console.log(stdout);
  //   console.log(stderr);
  //   // cb(err);
  // });

  // return exec(`bin/console server:start ${serverAddress} & echo`);
});

gulp.task('server:stop', () => {
  // exec(`bin/console server:stop ${serverAddress} &`);
  // child.spawn('bin/console', [ 'server:stop', serverAddress], { stdio: 'inherit' });
  // stopLocalServer();
  return gulp.src('app/Resources/views/**/*')
             // .pipe(gulpExec(`bin/console server:start ${serverAddress} &`)).on('start', () => {
             .pipe(gulpExec(`php -S ${serverAddress} &`)).on('end', () => {
      // .pipe(gulpExec('ls -lah').on('start', () => {
      console.log('\n');
      console.log(`Built in PHP server started on ${serverAddress}`);
      console.log('\n');
    });

});

function startLocalServer() {
  // child.spawn('bin/console', [ 'server:start', serverAddress], { stdio: 'inherit' });
  exec(`bin/console server:start ${serverAddress} &`, function (err, stdout, stderr) {
    console.log('server started?');
  });
}

function stopLocalServer() {
  child.spawn('bin/console', [ 'server:stop', serverAddress], { stdio: 'inherit' });
  // exec(`bin/console server:stop ${serverAddress} &`, function (err, stdout, stderr) {
  //   console.log(stdout);
  //   console.log(stderr);
  // });

}

function generateCriticalCss(pageName, url) {
  child.spawn('bin/console', [ 'server:start', serverAddress], { stdio: 'inherit' });

  const name = pageName || 'default';
  // critical.generate(
  //   {
  //     inline: false,
  //     base: 'app/Resources/views/critical',
  //     src: url,
  //     dest: `critical-css-inline-${name}.css.twig`,
  //     include: [/^.*-js$/],
  //     minify: true,
  //     dimensions: [
  //       {
  //         height: 400,
  //         width: 729
  //       },
  //       {
  //         height: 800,
  //         width: 899
  //       },
  //       {
  //         height: 1000,
  //         width: 1199
  //       },
  //       {
  //         height: 1000,
  //         width: 1201
  //       }
  //     ]
  //   }
  // );
}
