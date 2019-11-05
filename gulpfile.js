const gulp = require('gulp'),
    sass = require('gulp-sass'),
    concat = require('gulp-concat'),
    replace = require('gulp-replace'),
    autoprefixer = require('gulp-autoprefixer'),
    sourcemaps = require('gulp-sourcemaps'),
    cleanCSS = require('gulp-clean-css'),
    rename = require('gulp-rename'),
    uglify = require('gulp-uglify'),
    babel = require('gulp-babel'),
    gap = require('gulp-append-prepend'),
    touch = require('gulp-touch-cmd'),
    tar = require('gulp-tar'),
    gzip = require('gulp-gzip'),
    pkg = require('./package.json')

sass.compiler = require('sass')

const Paths = {
    SOURCE_COPY: [
        'config/**/*',
        'data/**/*',
        'icons/**/*',
        'images/**/*',
        'lang/**/*',
        'lang/**/*',
        'svg/**/*',
        'templates/**/*',
        'stylesheets/**/*',
        '*.php',
        'plugin.json',
        'README.md',
        'LICENSE',
        'CHANGELOG.md',
    ],
    SOURCE_BI_SVG: [
        'node_modules/bootstrap-italia/dist/svg/sprite.svg',
    ],
    SOURCE_SCSS: 'src/scss/' + pkg.name + '.scss',
    SOURCE_JS: [
        'src/js/plugins/font-path.js',
        'node_modules/bootstrap-italia/src/js/plugins/fonts-loader.js',
        'src/js/plugins/site-name.js',
        'src/js/plugins/pa-name.js',
        'src/js/plugins/search-input.js',
        'src/js/' + pkg.name + '.js',
    ],
    RELEASE_DIST: 'dist',
    DIST: 'dist/WAIMatomoTheme',
}

const waiBootstrapItaliaBanner = [
    '/*!',
    ' * ' + pkg.description,
    ' * @version v' + pkg.version,
    ' * @link ' + pkg.homepage,
    ' * @license ' + pkg.license,
    ' */',
    '',
].join('\n')

gulp.task('scss-min', () => {
    return gulp
        .src(Paths.SOURCE_SCSS)
        .pipe(sourcemaps.init())
        .pipe(sass().on('error', sass.logError))
        .pipe(autoprefixer())
        .pipe(
            cleanCSS({
                level: 2,
                specialComments: 'all',
            })
        )
        .pipe(gap.prependText(waiBootstrapItaliaBanner))
        .pipe(
            rename({
                suffix: '.min',
            })
        )
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(Paths.DIST + '/stylesheets'))
        .pipe(touch())
})

gulp.task('js-min', () => {
    return gulp
        .src(Paths.SOURCE_JS)
        .pipe(concat(pkg.name + '.js'))
        .pipe(sourcemaps.init())
        .pipe(replace(/^(export|import).*/gm, ''))
        .pipe(
            babel({
                compact: true,
                presets: [
                    [
                        '@babel/env',
                        {
                            modules: false,
                            loose: true,
                            exclude: ['transform-typeof-symbol'],
                        },
                    ],
                ],
                plugins: ['@babel/plugin-proposal-object-rest-spread'],
            })
        )
        .pipe(uglify())
        .pipe(gap.prependText(waiBootstrapItaliaBanner))
        .pipe(
            rename({
                suffix: '.min',
            })
        )
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(Paths.DIST + '/javascripts'))
        .pipe(touch())
})

gulp.task('copy', () => {
    return gulp
        .src(Paths.SOURCE_COPY, {
            base: '.',
        })
        .pipe(gulp.dest(Paths.DIST))
})

gulp.task('import-fonts', () => {
    return gulp
        .src(['node_modules/bootstrap-italia/src/fonts/**'])
        .pipe(gulp.dest(Paths.DIST + '/fonts'))
        .pipe(touch())
})

gulp.task('import-bi-svg', () => {
    return gulp
        .src(Paths.SOURCE_BI_SVG)
        .pipe(gulp.dest(Paths.DIST + '/svg'))
})

gulp.task('zip', () => {
    return gulp
        .src([
            Paths.RELEASE_DIST + '/**/*',
            '!' + Paths.RELEASE_DIST + '/**/*.tar.gz'
        ])
        .pipe(tar('wai-matomo-theme_' + pkg.version + '_auto_activate.tar', { mode: null }))
        .pipe(gzip())
        .pipe(gulp.dest(Paths.RELEASE_DIST))
})

gulp.task(
    'import-assets',
    gulp.series(
        'import-bi-svg',
        'import-fonts',
    )
)

gulp.task(
    'build',
    gulp.series(
        'copy',
        'import-assets',
        'scss-min',
        'js-min',
    )
)

gulp.task('release', gulp.series('build', 'zip'))
