/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function EIMZOMobile(site_id, qrcode_element){
    
    this.site_id = site_id;
    this.qrcode_element = qrcode_element;    
        
    var qrcode = new QRCode(qrcode_element, {
        width : 300,
        height : 300
    });
    
    this.makeQRCode = function(doc_num, text) {

        if (!this.site_id) {
            return;
        }
        if (!doc_num) {
            return;
        }
        if (!text) {
            return;
        }
        
        var hasher = new GostHash();
        var text_hash = hasher.gosthash(text);
        
        var code = this.site_id + doc_num + text_hash;
        var crcer = new CRC32();

        var crc32 = crcer.calcHex(code);
        code += crc32;
        
        qrcode.makeCode(code);
        
        return [text_hash,code];
                
    };


}
