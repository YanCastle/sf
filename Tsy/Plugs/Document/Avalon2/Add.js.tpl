/**
 * Created by castle on 2017/3/11.
 */
define(['avalon'],function (avalon) {
    var vm = avalon.define({
        $id:'{$Object}Add',
        $data:{
            //对象数据结构
        },
        data:{

        },
        ready:function (n,p) {
            if(n>0){
                this.data.{$Object}ID=n;
                this.get()
            }else{
                avalonData(this.data,[this.$data])
            }
        },
        get:function () {
            $$.apply(this,[
                '{$Object}/get',
                {{$Object}ID:this.data.{$Object}ID},
                function (d) {
                    this.data=d
                    // this.$origin=d
                }
            ])
        },
        save:function () {
            if(this.data.{$Object}ID>0){
                $$.apply(this,[
                    '{$Object}/save',{
                        {$Object}ID:this.data.{$Object}ID,
                        Params:this.data
                    },function (d) {
                        goto(-1)
                    }
                ])
            }else{
                $$.apply(this,[
                    '{$Object}/add',
                    this.data,
                    function (d) {
                        goto(-1)
                    },function (e) {
                        castle.error(e)
                    }
                ])
            }
        }
    })
    return vm;
})