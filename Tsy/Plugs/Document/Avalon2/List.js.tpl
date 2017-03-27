/**
 * Created by 鄢鹏权 on 2017/03/22.
 */
define(['avalon'],function (avalon) {
    var vm = avalon.define({
        $id:'{$Object}List',
        Where:{

        },
        Keyword:'',
        Sort:'',
        search:{
          L:[],T:0,P:0,N:10
        },
        ready:function (n,p) {
            if(n<1)n=1;
            this.search.P=n;
        },
        get:function () {
            $$.apply(this,[
                '{$Object}/search',{
                    P:this.search.P,
                    N:this.search.N,
                    Sort:'',
                    Keyword:this.Keyword,
                    W:this.Where
                },
                function (d) {
                    this.search=d
                },
                function (e) {
                    castle.notify(e)
                }
            ])
        },
        del:function ({$Object}ID) {
            if({$Object}ID>0){
                castle.confirm('确认删除?',function (d) {
                    $$.apply(this,[
                        '{$Object}/del',{
                            '{$Object}ID':{$Object}ID
                        },function (d) {
                            vm.get()
                        }
                    ])
                })
            }
        }
    });
    vm.$watch('search.P',function (n,o) {
        vm.get()
    });
    return vm;
})