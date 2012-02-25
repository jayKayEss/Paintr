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
            this.walk(0, 0, w, h, 0);
        },
        
        walk: function(x, y, w, h, from) {
            this.iterations++;
            console.log(this.iterations, x, y, w, h, from);

            $.ajax({
                
            });

            this.ctx.fillStyle = color;
            this.ctx.fillRect(x, y, w, h);

            if (w <= 1 && h <= 1) {
                return;
            }
            
            var newW = Math.ceil(w/2);
            var newH = Math.ceil(h/2);
            
            this.walk(0, 0, newW, newH, from); // TL
            this.walk(newW, 0, newW, newH); // TR
            this.walk(newW, newH, newW, newH); // BR
            this.walk(0, newH, newW, newH); // BL
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