var gulp = require('gulp');
var less = require('gulp-less');
var plumber = require('gulp-plumber');
var path = require('path');

var files = {
    less: [
        path.join(__dirname, 'static/less/*.less')
    ],
    css: path.join(__dirname, 'static/css/*.css'),
    js: path.join(__dirname, 'static/js/*.js'),
    dist_css: [
        path.join(__dirname, 'node_modules/bootstrap/dist/css/bootstrap.min.css')
    ],
    dist_js: [
        path.join(__dirname, 'node_modules/jquery/dist/jquery.min.js'),
        path.join(__dirname, 'node_modules/bootstrap/dist/js/bootstrap.min.js')
    ]
};

var dest = {
    less: path.join(__dirname, 'static/less'),
    css: path.join(__dirname, 'static/css'),
    dist: path.join(__dirname, 'static/lib')
};

gulp.task('less:css', function() {
    gulp.src(files.less)
        .pipe(plumber(function(error) {
            gutil.log(gutil.colors.red(error.message));
            gutil.beep();
            this.emit('end');
        }))
        .pipe(less({
            errorToConsole: true
        }))
        .pipe(gulp.dest(dest.css));
});

gulp.task('lib', function(){
    gulp.src(files.dist_css)
        .pipe(plumber(function(error){
            gutil.log(gutil.colors.red(error.message));
            gutil.beep();
            this.emit('end');
        }))
        .pipe(gulp.dest(dest.dist));
    
    gulp.src(files.dist_js)
        .pipe(plumber(function(error){
            gutil.log(gutil.colors.red(error.message));
            gutil.beep();
            this.emit('end');
        }))
        .pipe(gulp.dest(dest.dist));        
    
});

gulp.task('watch', function() {
    gulp.watch(files.less, ['less:css']);
});

gulp.task('default', ['less:css','lib','watch']);