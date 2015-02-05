<?php
include ("../../../inc/includes.php");

//change mimetype
header("Content-type: application/javascript");

//not executed in self-service interface & right verification
if ($_SESSION['glpiactiveprofile']['interface'] == "central") {
   if (Session::haveRight("ticket", CREATE) 
      || Session::haveRight("ticket", UPDATE) 
   ) {
   
   $locale_actor = __('Actor');
   $locale_group_view = __('Group View');

   $JS = <<<JAVASCRIPT

   tickets_id = null;
   
   function ticketEscalation() {
      var url = '../plugins/escalade/ajax/history.php';
      
      //set active group in red
      $("table:contains('$locale_actor') td:last-child a[href*=group]").addClass('escalade_active');

      //add new histories in assign actor
      $.ajax({
         type: "POST",
         url: url,
         data: {'tickets_id': tickets_id},
         success: function(response, opts) {
            if ($(".escalade_active:last").length > 0) {
               $(".escalade_active:last").after(response);
            } else {
               //OLD : assign_bloc.insertHtml("beforeEnd", response.responseText);
               $("table:contains('$locale_actor') td:last-child").append(response);
            }
            
         }
      });
   }
   
   // only on central page
   if (location.pathname.indexOf('central.php') > 0) {
      $(document).on("tabsload", function() {

         var suffix = "";
         var selector = "#ui-tabs-2 .tab_cadre_central .top:last" +
            ", .alltab:contains('$locale_group_view') + .tab_cadre_central .top:last";
         
         // get central list for plugin and insert in group tab
         $(selector).each(function(){
            
            if (this.innerHTML.indexOf('escalade_block') < 0) {
               
               //if (option.url.indexOf("-1") > 0) { //option.params
               //   suffix = "_all";
               //}
               
               //prepare a span element to load new elements
               $(this).prepend("<span id='escalade_block"+suffix+"'>test</span>");
               
               //ajax request
               selectorbis = "#escalade_block"+suffix;
               //console.log(selectorbis);
               $(selectorbis).load('../plugins/escalade/ajax/central.php');
            }
         });
      });
   }
   
   // only in ticket form
   if (location.pathname.indexOf('ticket.form.php') > 0) {
      $('#tabspanel + div.ui-tabs').on("tabsload", function( event, ui ) {
   
         tickets_id = getUrlParameter('id');
         
         //only in edit form
         if (tickets_id == undefined) return;
         
         setTimeout(function() {
            ticketEscalation();
         }, 300);
      });
   }
JAVASCRIPT;
      echo $JS;
   }
}