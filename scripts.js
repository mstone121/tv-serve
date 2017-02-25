"use strict";

let video;

window.onload = function() {
    video = document.getElementById("player");

    $("#commercial-destroy").click(commercialDestroy);
    $("#fix").click(commercialBack);
    video.ontimeupdate = updateProgressBar;

    $("h3").each(function(index, element) {
        const jElement = $(element);
        const text = jElement.text();

        jElement.html('');
        jElement.html(colorText(text, 0, 5, 90, 50));
    });
};

function colorText(text, startHue, increment = 5, saturation = 90, lightness = 90) {
    let html = '';
    for (let index in text) {
        html += [
            '<span style="color: hsl(', startHue,
            ', ', saturation, '%, ', lightness,
            '%);">', text[index], '</span>'
        ].join('');

        startHue += increment;
    };

    return html;
}

// Player Controls
function commercialBack() {
    video.currentTime = video.currentTime - 5;
}
function commercialDestroy() {
    video.currentTime = video.currentTime + 31;
}
function rewind(seconds) {
    video.currentTime = video.currentTime - seconds;
}
function pause() {
    if (video.paused) {
        video.play();
    } else {
        video.pause();
    }
}
function fullScreen() {
    if (video.requestFullscreen) {
        video.requestFullscreen();
    } else if (video.mozRequestFullScreen) {
        video.mozRequestFullScreen();
    } else if (video.webkitRequestFullscreen) {
        video.webkitRequestFullscreen();
    }
}

window.onkeypress = function({ charCode: code }) {
    if      (!video)      return;
    else if (code === 44) commercialBack();
    else if (code === 46) commercialDestroy();
    else if (code > 48 && code < 58) rewind(code - 48);
    else if (code === 32) pause();
    else if (code === 13) fullScreen();
};

