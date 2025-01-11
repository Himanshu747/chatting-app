/**
 * -------------------------------------------
 * Resuable Functions
 * ------------------------------------------
 */
function enableChatBoxLoader(){
    $(".wsus__message_paceholder").removeClass('d-none');
}
function disableChatBoxLoader(){
    $(".wsus__message_paceholder").addClass('d-none');
}

function imagePreview(input,selector){
    if(input.files && input.files[0]){
        let render=new FileReader();

        render.onload=function(e){
            $(selector).attr('src',e.target.result);
        }
        render.readAsDataURL(input.files[0]);
    }
}
let searchPage=1;
let noMoreDataSearch=false;
let searchTempVal="";
let setSearchLoading=false;
function searchUsers(query){
    /**
     * Issue was facing while searching whenever we searching anything it was working fine
     * Second time we try to search it was facing issue while searching so it fixed by below code start
     */
    if(query != searchTempVal){
        searchPage=1;
        noMoreDataSearch=false;
    }
    /**
     * Issue was facing while searching whenever we searching anything it was working fine
     * Second time we try to search it was facing issue while searching so it fixed by below code end
     */

    searchTempVal=query;

    if(!setSearchLoading && !noMoreDataSearch){
        $.ajax({
            method:'GET',
            url:'/messenger/search',
            data:{query:query,page:searchPage},
            beforeSend:function(){
                setSearchLoading=true;
                let loader=`<div class="text-center search-loader"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>`;
                $('.user_search_list_result').append(loader);
            },
            success:function(data){
                setSearchLoading=false;
                $('.user_search_list_result').find('.search-loader').remove();
                if(searchPage < 2){
                    $('.user_search_list_result').html(data.records);
                }
                else{
                    $('.user_search_list_result').append(data.records);
                }
           
                noMoreDataSearch = searchPage >= data?.last_page
                if(noMoreDataSearch) searchPage+=1;
            },
            error:function(xhr,status,error){
                setSearchLoading=false;
                $('.user_search_list_result').find('.search-loader').remove();
            }
        });
    }
    
}

function actionOnScroll(selector,callback,topScroll=false){
    $(selector).on('scroll',function(){
        let element=$(this).get(0);
        const condition=topScroll ? element.scrollTop==0 : element.scrollTop + element.clientHeight >= element.scrollHeight;

        if(condition){
            callback();
        }
    });
}
function debounce(callback,delay){
    let timerId;
    return function(...args){
        clearTimeout(timerId);
        timerId=setTimeout(()=>{
            callback.apply(this,args);
        },delay);
    }
}
/**
 * -------------------------------------------
 * Fetch id data of user and update the view
 * ------------------------------------------
 */
function IDinfo(id){
    $.ajax({
        method:"GET",
        url:'messenger/id-info',
        data:{id:id},
        beforeSend:function(){
            NProgress.start();
            enableChatBoxLoader();
        },
        success:function(data){
            console.log(data);
            $('.messenger-header').find("img").attr("src",data.getuserinfo.avatar);
            $('.messenger-header').find("h4").text(data.getuserinfo.name);

            $('.messenger-info-view .user_photo').find("img").attr("src",data.getuserinfo.avatar);
            $('.messenger-info-view').find(".user_name").text(data.getuserinfo.name);
            $('.messenger-info-view').find(".user_unique_name").text(data.getuserinfo.user_name);
            NProgress.done();
            disableChatBoxLoader();
        },
        error:function(xhr,status,error){
            disableChatBoxLoader();
        }
    })
}
/**
 * -------------------------------------------
 * On DOM Load
 * ------------------------------------------
 */
$(document).ready(function(){
    $('#select_file').change(function(){
        imagePreview(this,'.profile-image-preview');
    });
     //Search action on keyup
     const debounceSearch= debounce(function(){
        const value=$('.user_search').val();
        searchUsers(value);
     },500);   
    $('.user_search').on('keyup',function(){
        let query=$(this).val();
       if(query.length>0){
            debounceSearch();
       }
    });

    //search pagination
    actionOnScroll(".user_search_list_result",function(){
        let value=$('.user_search').val();
         searchUsers(value);
    });

    //click action for messenger list item
    $("body").on("click",".messenger-list-item",function(){
        //alert('selected');
        const dataId=$(this).attr("data-id");
        IDinfo(dataId); 
    });
});