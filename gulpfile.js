'use strict';

const gulp = require('gulp');
const shell = require('shelljs');
const del = require('del');

const config = {
    svn: {
        url: 'http://plugins.svn.wordpress.org/random-post-on-refresh/',
        src: [
            './**',
            '!**/svn',
            '!**/svn/**',
            '!**/readme.md',
            '!**/package.json',
            '!**/node_modules',
            '!**/node_modules/**',
            '!**/bower.json',
            '!**/bower_components',
            '!**/bower_components/**',
            '!**/gulpfile.js',
            '!**/gulp',
            '!**/gulp/**',
            '!**/*.json',
            '!**/*.lock'
        ],
        dest: './svn/trunk',
        clean: './svn/trunk/**/*'
    }
};

gulp.task('checkout', (done) => {
    shell.exec('svn co ' + config.svn.url + ' svn');
    done();
});

gulp.task('clean', () => {
    return del(config.svn.clean);
});

gulp.task('copy', () => {
    return gulp.src(config.svn.src).pipe(gulp.dest(config.svn.dest));
});

gulp.task('stage', gulp.series('clean', 'copy'));

gulp.task('default', gulp.series('stage'));
