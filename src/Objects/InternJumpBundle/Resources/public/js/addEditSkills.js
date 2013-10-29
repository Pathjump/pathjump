$(document).ready(function(){
    //autocomplete skill search
    $("#skillsSearch").autocomplete({
        minLength: 3,
        source: function(request, response) {
            $.ajax({
                url: searcSkillsUrl,
                data: {
                    term: request.term
                },
                dataType: "json",
                success: function(data) {
                    // alert(data);
                    response($.map(data, function(item) {

                        return {
                            label:  item,
                            value:  item

                        }
                    }))
                }
            })
        },
        select: function( event, ui ) {
            result = ui.item.value;
            addSkill(result);
        },
        close: function (event, ui){
//            $("#skillsSearch").val("");
        }
    });

    //add new skill
    $('#addSkill').click(function(){
        var skillName = $.trim($('#skillsSearch').val().toLowerCase());
        if(skillName){
            addSkill(skillName);
            $('#skillsSearch').val('');
        }
    });

    //delete skill
    $('a#deleteSkill').live('click',function(){
        thisLink = $(this);
        //check if the skill saved in database
        if(thisLink.attr('skillId')){
            //hide the link
            thisLink.hide();
            //show loading div
            $('.loading').show();
            //remove the skill
            $.ajax({
                url: userRemoveSkillurl+"/"+thisLink.attr('skillId'),
                success: function(msg) {
                    if(msg == 'done'){
                        thisLink.parent().remove();
                        var skillName = thisLink.parent().find('#skillName').text();
                        //remove this skill from array
                        removeSkill(skillName);
                    }
                },
                complete: function(msg) {
                    //hide loading div
                    $('.loading').hide();
                }
            });
        }else{
            var skillName = thisLink.parent().find('#skillName').text();
            thisLink.parent().remove();
            //remove this skill from array
            removeSkill(skillName);
        }
    });

    //submit the user skills
    $('#submitSkills').click(function(){
        //disable the button
        $(this).attr('disabled','disabled');
        //show loadin img
        $('.loading').show();
        $.ajax({
            url: saveUserSkillsUrl+"/"+skillArray,
            success: function(msg) {
            },
            complete: function(msg) {
                window.location = userHomePageUrl;
            }
        });
    });
});

function removeSkill(skill){
    var index = skillArray.indexOf(skill);
    skillArray.splice(index, 1);
    if(skillArray.length == 0){
        //disable the submit button
        $('#submitSkills').attr('disabled','disabled');
    }
}

function addSkill(skill){
    //check if the user add this skill before
    if($.inArray(skill, skillArray) >= 0){
        //show error span
        $('.skillError').show();
        $('.skillError').text('You have duplicate skills.');
    }else{
        //hide error span
        $('.skillError').hide();
        //add the new skill
        skillArray.push(skill);
        $('#skillsResult').append('<li><span id="skillName">'+skill+'</span><a href="javascript:void(0)" id="deleteSkill">x</a></li>');
        //enable the submit button
        $('#submitSkills').removeAttr('disabled');
    }
}