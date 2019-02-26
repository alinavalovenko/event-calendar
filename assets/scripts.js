jQuery(document).ready(function ($) {
   var yearLink = $(".avec-link-toggle");

    yearLink.click(function (event) {
        event.preventDefault();
        var avecTable = $(this).parent(".calendar-year").find(".event-table");
        if(avecTable.hasClass("avec-hide")) {
            $(this).addClass("opened");
            avecTable.removeClass("avec-hide");
        } else{
            $(this).removeClass("opened");
            avecTable.addClass("avec-hide");}
    });

});