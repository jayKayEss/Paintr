(function($){
    
    var Paintr = function(name) {
        this.elem = $(name);
        this.canvas = this.elem.get(0);
        this.ctx = this.canvas.getContext("2d");
        // ctx.fillStyle="#FF0000";
        // ctx.fillRect(0,0,150,75);
    };
    
    Paintr.prototype = {

        paint: function() {
            var w = this.canvas.width;
            var h = this.canvas.height;
            this.iterations = 0;
            this.walk(0, 0, w, h, 0, 0);
        },
        
        walk: function(x, y, w, h, from, pos) {
            var self = this;
            $.ajax('srv/getcolor.php', {
                data: {
                    'from': from,
                    'pos': pos
                },
                success: function(data) {
                    var color = data.term + '';
                    var colorId = data.id;
                    
                    // console.log(color, x, y, w, h, from, colorId);
                    
                    self.ctx.fillStyle = color;
                    self.ctx.fillRect(x, y, w, h);

                    if (w <= 1 || h <= 1) {
                        return;
                    }

                    var hw = Math.ceil(w/2);
                    var hh = Math.ceil(h/2);

                    setTimeout(function(){

                        // top-left
                        self.walk(x, y, hw, hh, colorId, 1);
                        // top-right
                        self.walk(x+hw, y, hw, hh, colorId, 2);
                        // bottom-right
                        self.walk(x+hw, y+hh, hw, hh, colorId,3 );
                        // bottom-left
                        self.walk(x, y+hh, hw, hh, colorId, 4);

                    }, 100)
                }
            });


        },
        
        randColor: function() {
            var r = Math.ceil(Math.random() * 256);
            var g = Math.ceil(Math.random() * 256);
            var b = Math.ceil(Math.random() * 256);
            
            return 'rgb('+r+','+g+','+b+')';
        }
        
    };
    
    $(document).ready(function(){
        var paintr = new Paintr('#theCanvas');
        
        $('#go').click(function(){
            paintr.paint();
        });
    });
    
})(jQuery);