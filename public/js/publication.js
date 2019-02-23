function myFunction($id) {

    var x = document.getElementById("posterCommentaire"+$id);


    if (x.style.display === "none") {
        x.style.display = "block";
    } else {
        x.style.display = "none";
    }
}

function afficheTrack() {

    var x = document.getElementById("tracks");


    if (x.style.display === "none") {
        x.style.display = "block";
    } else {
        x.style.display = "none";
    }
}


$(document).ready(function () {

    $('.js-like-comment').on('click',function (e) {
        e.preventDefault();

        var $link = $(e.currentTarget);

        $link.toggleClass('fa fa-gittip text-danger').toggleClass('fa fa-gittip');

        var $comCount = $link.find('.js-like-comment-count');

        $.ajax({
            method: 'POST',
            url: $link.attr('href'),
            data: {
                "type": 1
            }
        }).done(function (data) {
            $comCount.html(data.flows)
        });


    });


    $('.js-like-publication').on('click',function (e) {
        e.preventDefault();

        var $link = $(e.currentTarget);

        $link.toggleClass('fa fa-gittip').toggleClass('fa fa-gittip text-danger');

        var $publiCount = $link.find('.js-like-publication-count');

        $.ajax({
            method: 'POST',
            url: $link.attr('href'),
            data: {
                "type": 1
            }
        }).done(function (data) {
            $publiCount.html(data.flows)
        });


    });


    $('.js-follow').on('click',function (e) {
        e.preventDefault();

        var $link = $(e.currentTarget);

        $link.toggleClass('btn btn-success btn-sm').toggleClass('btn btn-danger btn-sm');

        $.ajax({
            method: 'POST',
            url: $link.attr('href')
        }).done(function (data) {
            $('.js-follow-status').html(data.status)
        });


    });


    $('.js-playlist').on('click',function (e) {

        e.preventDefault();

        var $link = $(e.currentTarget);

        $link.toggleClass('fa fa-angle-right').toggleClass('fa fa-angle-down');

        $id = $link.attr('datatype');

        var x = document.getElementById("playlist"+$id);

        var test = $(x);

        var $namePlaylist = test.find('.namePlaylist');
        var $descriptionPlaylist = test.find('.descriptionPlaylist');
        var $imgPlaylist = test.find('.imgPlaylist');
        var $followersPlaylist = test.find('.followersPlaylist');
        var $embedPlaylist = test.find('.embedPlaylist');

        if (x.style.display === "none") {
            x.style.display = "block";
        } else {
            x.style.display = "none";
        }

        $.ajax({
            method: 'POST',
            url: $link.attr('href'),
        }).done(function (data) {
            $imgPlaylist.html(data.imgPlaylist)
            $namePlaylist.html(data.namePlaylist)
            $descriptionPlaylist.html(data.descriptionPlaylist)
            $followersPlaylist.html(data.followersPlaylist)
            $embedPlaylist.html(data.embedPlaylist)
        });



    });

    $('.js-like-musique').on('click',function (e) {
        e.preventDefault();

        var $link = $(e.currentTarget);

        $link.toggleClass('fa-heart-o').toggleClass('fa-heart text-danger');

        $.ajax({
            method: 'POST',
            url: $link.attr('href'),
            data: {
                "type": 1
            }
        }).done(function (data) {
        });


    });

    $('.js-like-album').on('click',function (e) {
        e.preventDefault();

        var $link = $(e.currentTarget);

        $link.toggleClass('fa-heart-o').toggleClass('fa-heart text-danger');

        $.ajax({
            method: 'POST',
            url: $link.attr('href'),
            data: {
                "type": 1
            }
        }).done(function (data) {
        });


    });


    $('.js-like-artiste').on('click',function (e) {
        e.preventDefault();

        var $link = $(e.currentTarget);

        $link.toggleClass('fa-heart-o').toggleClass('fa-heart text-danger');

        $.ajax({
            method: 'POST',
            url: $link.attr('href'),
            data: {
                "type": 1
            }
        }).done(function (data) {
        });


    });

    $('.js-like-playlist').on('click',function (e) {
        e.preventDefault();

        var $link = $(e.currentTarget);

        $link.toggleClass('fa-heart-o').toggleClass('fa-heart text-danger');

        $.ajax({
            method: 'POST',
            url: $link.attr('href'),
            data: {
                "type": 1
            }
        }).done(function (data) {
        });


    });


});