jQuery(document).ready(
    function() {
        let objMessage = jQuery('.qlform.message.alert');
        jQuery.each(objMessage, function (index, value) {
            if ('' === objMessage.text().trim()) {
                jQuery(objMessage[index]).addClass('hidden');
            }
        });


        jQuery(document).on('click', '.qlform button.submit', function (e) {
            e.preventDefault();
            let objButton = jQuery(e.target);
            let objForm = jQuery(objButton.closest('form'));
            let objMessage = objForm.closest('.qlformContainer').find('.message.alert');
            let strData = objForm.serialize();
            let moduleId = 146;
            if (false) {
                console.log(objForm);
                console.log(strData);
            }
            let strUrl = 'index.php?option=com_ajax&module=qlform&method=recieveQlform&format=json';
            let objParams = {
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
                    qlformAfterSend(moduleId);
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
