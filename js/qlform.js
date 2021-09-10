jQuery(document).ready(
    function() {
        var objMessage = jQuery('.qlform.message.alert');
        jQuery.each(objMessage, function (index, value) {
            if ('' === objMessage.text().trim()) {
                jQuery(objMessage[index]).addClass('hidden');
            }
        });


        jQuery(document).on('click', '.qlform button.submit', function (e) {
            e.preventDefault();
            var objButton = jQuery(e.target);
            var objForm = jQuery(objButton.closest('form'));
            var objMessage = objForm.closest('.qlformContainer').find('.message.alert');
            var strData = objForm.serialize();
            if (false) {
                console.log(objForm);
                console.log(strData);
            }
            var strUrl = 'index.php?option=com_ajax&module=qlform&method=recieveQlform&format=json';
            var objParams = {
                url: strUrl,
                type: 'post',
                dataType: 'json',
                format: 'raw',
                method: 'post',
                data: strData,
                async: true,
                success: function (objResult) {
                    objMessage.html(objResult.message);
                    console.log(objResult);
                    objMessage.removeClass('hidden');
                    // objForm.addClass('hidden');
                    // console.log(objResult);
                    // console.log('success');
                },
                error: function (objResult) {
                    console.log('fail');
                    objMessage.html(objResult.message);
                    objMessage.removeClass('hidden');
                    objForm.removeClass('hidden');
                    // console.log(objResult);
                }
            };
            jQuery.ajax(objParams);
        });
    }
);
