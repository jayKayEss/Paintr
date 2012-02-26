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
            this.walk(0, 0, w, h, 0, 0);
        },
        
        walk: function(mainx, mainy, mainw, mainh, from, pos) {
            var self = this;
            var max_depth = 8;
            
            $.ajax('srv/getimage.php', {
                data: {
                    'max': max_depth,
                    'width': mainw,
                    'height': mainh,
                    'from': from,
                    'pos': pos
                },
                success: function(data) {
                    if (data) {
                        for (i in data) {
                            var rec = data[i];
              
                            var x = rec[0];
                            var y = rec[1];
                            var w = rec[2];
                            var h = rec[3];
                            var color = rec[4];
                            var depth = rec[5];
                            var pos = rec[6];
                            var color_id = rec[7];

                            self.ctx.fillStyle = color;
                            self.ctx.fillRect(
                                mainx+x, mainy+y,
                                w, h
                            );

                            // if (depth == max_depth &&
                            //     w > 4 && h > 4) {
                            //     self.goDeeper(
                            //         mainx+x, mainy+y, 
                            //         w, h, 
                            //         color_id, pos
                            //     );
                            // }
                        }
                    }
                }
            });
        },
        
        goDeeper: function(x, y, w, h, color_id, pos) {
            var self = this;
            setTimeout(function(){
                self.walk(x, y, w, h, color_id, pos);
            })
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
        
        setInterval(function(){
            paintr.paint();
        }, 10000);
        
    });
    
})(jQuery);