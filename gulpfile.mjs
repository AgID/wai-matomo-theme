import gulp from 'gulp';
import gulpSass from 'gulp-sass';
import dartSass from 'sass'; // Importa Dart Sass
import concat from 'gulp-concat';
import replace from 'gulp-replace';
import autoprefixer from 'gulp-autoprefixer';
import sourcemaps from 'gulp-sourcemaps';
import cleanCSS from 'gulp-clean-css';
import rename from 'gulp-rename';
import uglify from 'gulp-uglify';
import babel from 'gulp-babel';
import gap from 'gulp-append-prepend';
import touch from 'gulp-touch-cmd';
import tar from 'gulp-tar';
import gzip from 'gulp-gzip';
import clean from 'gulp-clean';
import gulpif from 'gulp-if';
import { readFile } from 'fs/promises';

// Configura il compilatore di gulp-sass
const sass = gulpSass(dartSass);

// Leggi il package.json
const pkg = JSON.parse(await readFile(new URL('./package.json', import.meta.url)));

// Configura i percorsi
const Paths = {
    SOURCE_COPY: [
        'config/**/*',
        'data/**/*',
        'icons/**/*',
        'images/**/*',
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
};

// Banner per i file generati
const waiBootstrapItaliaBanner = [
    '/*!',
    ' * ' + pkg.description,
    ' * @version v' + pkg.version,
    ' * @link ' + pkg.homepage,
    ' * @license ' + pkg.license,
    ' */',
    '',
].join('\n');

// Task: Pulizia della directory di distribuzione
gulp.task('clean', () => {
    return gulp.src(Paths.RELEASE_DIST, { read: false, allowEmpty: true }).pipe(clean());
});

// Task: Compilazione e minimizzazione SCSS
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
        .pipe(gulpif(process.env.NODE_ENV != 'production', sourcemaps.write('.')))
        .pipe(gulp.dest(Paths.DIST + '/stylesheets'))
        .pipe(touch());
});

// Task: Concatenazione e minimizzazione JavaScript
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
        .pipe(gulpif(process.env.NODE_ENV != 'production', sourcemaps.write('.')))
        .pipe(gulp.dest(Paths.DIST + '/javascripts'))
        .pipe(touch());
});

// Task: Copia dei file statici
gulp.task('copy', () => {
    return gulp
        .src(Paths.SOURCE_COPY, {
            base: '.',
        })
        .pipe(gulp.dest(Paths.DIST));
});

// Task: Importazione dei font
gulp.task('import-fonts', () => {
    return gulp.src(['node_modules/bootstrap-italia/src/fonts/**']).pipe(gulp.dest(Paths.DIST + '/fonts')).pipe(touch());
});

// Task: Importazione SVG di Bootstrap Italia
gulp.task('import-bi-svg', () => {
    return gulp.src(Paths.SOURCE_BI_SVG).pipe(gulp.dest(Paths.DIST + '/svg'));
});

// Task: Compressione in formato tar.gz
gulp.task('zip', () => {
    return gulp
        .src([`${Paths.RELEASE_DIST}/**/*`, `!${Paths.RELEASE_DIST}/**/*.tar.gz`])
        .pipe(tar(`wai-matomo-theme_${pkg.version}_auto_activate.tar`, { mode: null }))
        .pipe(gzip())
        .pipe(gulp.dest(Paths.RELEASE_DIST));
});

// Task: Importazione degli asset
gulp.task('import-assets', gulp.series('import-bi-svg', 'import-fonts'));

// Task: Build completo
gulp.task('build', gulp.series('copy', 'import-assets', 'scss-min', 'js-min'));

// Task: Release
gulp.task('release', gulp.series('clean', 'build', 'zip'));
