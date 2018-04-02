// Gulp.js configuration
var gulp = require('gulp'),

plugins = require('gulp-load-plugins')({
  pattern: ['gulp-*', 'gulp.*', '*'],
  replaceString: /\bgulp[\-.]/,
      lazy: true,
      camelize: true
    }),

  // folders
  folder = {
    src: 'dev/',
    build: './'
  };

gulp.task('browser-sync', ['css'], function() {
    plugins.browserSync.init({
        proxy: "http://www.rextrophies.local/",
        // host: 'rextrophies.waimanwong.local',
        // open: 'external',
        notify: false,
        injectChanges: true,
        port: 8000

    });
});

// image processing
gulp.task('images', function() {
  var out = folder.build + 'images/';
  return gulp.src(folder.src + 'images/**/*')
    .pipe(plugins.newer(out))
    .pipe(plugins.imagemin({ optimizationLevel: 5 }))
    .pipe(gulp.dest(out));
});

// JavaScript processing
gulp.task('js', function() {

  var jsbuild = gulp.src([folder.src + 'js/**/*'])
  .pipe(plugins.deporder())
  .pipe(plugins.concat('scripts.js'))
  // .pipe(plugins.stripDebug())
  .pipe(plugins.uglify())
  .on('error', onError)
  .pipe(plugins.rename( { suffix: '.min' } ))
  return jsbuild.pipe(gulp.dest(folder.build + 'js/'));

});

// CSS processing
gulp.task('css', ['images'], function() {

  var postCssOpts = [
  plugins.autoprefixer({ browsers: ['last 2 versions', '> 2%'] }),
  // plugins.cssMqpacker,
  plugins.cssnano
  ];

  return gulp.src(folder.src + 'scss/main.scss')
    .pipe(plugins.sass({
      outputStyle: 'nested',
      imagePath: 'images/',
      precision: 3,
      errLogToConsole: true
    }))
    .on('error', onError)
    // .pipe(csssandbox('#sandbox'))
    .pipe(plugins.postcss(postCssOpts))
    .pipe(plugins.rename( { suffix: '.min' } ))
    .pipe(gulp.dest(folder.build + 'css/'))
    .pipe(plugins.browserSync.reload({stream: true}));

});

gulp.watch(folder.src + 'images/**/*', ['images']);
gulp.watch(folder.build + '**/**/*.php').on('change', plugins.browserSync.reload);
gulp.watch(folder.src + 'js/**/*', ['js']).on('change', plugins.browserSync.reload);
gulp.watch(folder.src + 'scss/**/*', ['css']);

//add an error listener to where your errors might be thrown.
// .on('error', onError)
function onError(err) {
  console.log(err);
  this.emit('end');
}

//watch for changes
gulp.task('watch', ['browser-sync'], function() {

  // image changes
  gulp.watch(folder.src + 'images/**/*', ['images']);

  // html changes
  // gulp.watch(folder.build + '**/*.html').on('change', plugins.browserSync.reload);

  // php changes
  gulp.watch(folder.build + '**/*.php').on('change', plugins.browserSync.reload);

  // javascript changes
  gulp.watch(folder.src + 'js/**/*', ['js']).on('change', plugins.browserSync.reload);

  // css changes
  gulp.watch(folder.src + 'scss/**/*', ['css']);

});

// default task
gulp.task('default', ['css', 'js', 'watch']);
