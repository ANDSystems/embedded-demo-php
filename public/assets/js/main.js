<!--Menu-->

// menu
$(document).ready(function () {
    $(".sub > a").click(function () {
        var ul = $(this).next(),
            clone = ul.clone().css({"height": "auto"}).appendTo(".mini-menu"),
            height = ul.css("height") === "0px" ? ul[0].scrollHeight + "px" : "0px";
        clone.remove();
        ul.animate({"height": height});
        return false;
    });
    $('.mini-menu > ul > li > a').click(function () {
        $('.sub a').removeClass('active');
        $(this).addClass('active');
    }),
        $('.sub ul li a').click(function () {
            $('.sub ul li a').removeClass('active');
            $(this).addClass('active');
        });
});


// add to cart button
$(function() {

    // list
    $.ajax({
        url: '/index.php/cart/list',
        success: function(response) {
            $('.cart-indicator').html(response);
        }
    });

    // cart add
    $(document).on('click', '.product-add-cart', function (e){
        e.preventDefault();

        // this
        var $this = $(this);


        // fly to cart effect
        var cart = $('.cart-indicator');
        var imgtodrag = $this.parents('.item:first').find("img:first");
        if (imgtodrag) {
            var imgclone = imgtodrag.clone()
                .offset({
                    top: imgtodrag.offset().top,
                    left: imgtodrag.offset().left
                })
                .css({
                    'opacity': '0.5',
                    'position': 'absolute',
                    'height': '150px',
                    'width': '150px',
                    'z-index': '100'
                })
                .appendTo($('body'))
                .animate({
                    'top': cart.offset().top + 10,
                    'left': cart.offset().left + 10,
                    'width': 75,
                    'height': 75
                }, 500);

            imgclone.animate({
                'width': 0,
                'height': 0
            }, function () {
                $(this).detach()
            });
        }



        $.ajax({
            url: '/index.php/cart/add?id=' + $this.data('id'),
            success: function(response) {
                $('.cart-indicator').html(response);
                $('.cart-indicator').effect('highlight');
            }
        });
    });



});