var PageoneCounter = {};
PageoneCounter.PageoneFieldCounter = function(limit) {this.baseInitialize(limit)};
 
PageoneCounter.PageoneFieldCounter.prototype = 
{
	 baseInitialize: function(limit) 
	 {
		Counter = 0;
		maxlimit = limit;
		smsSize = 160;
	 },

	textFieldCounter : function(field)  
	{
		if (field.length > maxlimit)  
		{
			field = field.substring(0, maxlimit);
			return field.length;
		}					
		else
		{
			Counter = field.length;
			return Counter;
		}	
	},

	smsCounter : function(field)  
	{
		ac = this.textFieldCounter(field);
		if(ac <1)
		{
			return 0;
		}
		return Math.ceil(ac/smsSize);
	},
	
	setSMSSize : function(size)
	{
		smsSize = size;
	},

	entriesCount : function(field, seperator)
	{
		try
		{
		  var src = field
		  if((src)&&(src.length>0))
		  {
			  myArray1 = (src).split(seperator);
			  return (myArray1.length);
		  }
		  return 0;
		}
		catch(Exception)
		{
		}
	}
};

PageoneCounter.PageoneSMSCounter = Class.create();
Object.extend(Object.extend(PageoneCounter.PageoneSMSCounter.prototype, PageoneCounter.PageoneFieldCounter.prototype), 
{
	initialize: function(limit) 
	{
		Counter = 0;
		maxlimit = limit;
		smsSize = 160;
	},
	
	displayCounter : function(field, displayCell)
	{
		cChars =  this.textFieldCounter(field)
                if (document.theform.includefrom.checked)
                 cChars=cChars+this.textFieldCounter(fromString);
		aCount = this.entriesCount($('address'), ";")
		smsCount =  this.smsCounter(field);
		
		var aTemp = "";
		if(aCount > 1)
		{
			aTemp = ", "+aCount+" addresses "
		}
		else
		{
		}
	
		var cTemp = "";
		if(smsCount > 1)
		{
			cTemp = ", "+smsCount+" sms messages"
		}
		else if(smsCount == 0)
		{
			cTemp = ", no sms message"
		}
		else
		{
			cTemp = ", "+smsCount+" sms message"
		}
		displayCell.innerHTML=""+cChars+" character(s) "+aTemp+cTemp;	
	}
});


PageoneCounter.PageoneCharMonitor = Class.create();
Object.extend(Object.extend(PageoneCounter.PageoneCharMonitor.prototype, PageoneCounter.PageoneFieldCounter.prototype), 
{
	initialize: function(limit) 
	{
		maxlimit = limit;
	},
	
	getExtendedChars : function(field)  
	{
		newVal = field;
		extChars = "";
		if(newVal != null)
		{
			for(x=0; x<newVal.length; x++)
			{
				tCharCode = newVal.charCodeAt(x);
                                /*******This gets mangled when sent out by Moodle
				c = ""+newVal.charAt(x);
				if((c == '{') || (c == '}') || (c == '\\') || (c == '^') || (c == '[') || (c == ']')
					|| (c == '£') || (c == '¥') || (c == 'è') || (c == 'é') || (c == 'ù') || (c == 'ì') || (c == 'ò')
					|| (c == 'ò') || (c == 'Ç') || (c == 'Ø') || (c == 'ø') || (c == 'Å') || (c == 'å') || (c == 'Æ')
					|| (c == 'Æ') || (c == 'æ') || (c == 'ß') || (c == 'É') || (c == '¡') || (c == 'Ä') || (c == 'Ö')
					|| (c == 'Ñ') || (c == 'Ü') || (c == '§') || (c == '¿') || (c == 'ä') || (c == 'ö') || (c == 'ñ') 
					|| (c == 'ü') || (c == 'à') || (c == '€'))
                                *******/
                                if ((tCharCode==123) || (tCharCode==125) || (tCharCode==92) || (tCharCode==94) || (tCharCode==91) ||
                                    (tCharCode==93) || (tCharCode==163) || (tCharCode==165) || (tCharCode==232) || (tCharCode==233) ||
                                    (tCharCode==249) || (tCharCode==236) || (tCharCode==242) || (tCharCode==242) || (tCharCode==199) ||
                                    (tCharCode==216) || (tCharCode==248) || (tCharCode==197) || (tCharCode==229) || (tCharCode==198) ||
                                    (tCharCode==198) || (tCharCode==230) || (tCharCode==223) || (tCharCode==201) || (tCharCode==161) ||
                                    (tCharCode==196) || (tCharCode==214) || (tCharCode==209) || (tCharCode==220) || (tCharCode==167) ||
                                    (tCharCode==191) || (tCharCode==228) || (tCharCode==246) || (tCharCode==241) || (tCharCode==252) ||
                                    (tCharCode==224) || (tCharCode==8364))
				{
				}
				else if(tCharCode > 127)
				{
					if(extChars.length>0)
						extChars += ", \'"+newVal.charAt(x)+"\'";
					else
						extChars += "\'"+newVal.charAt(x)+"\'";
				}				
			}
		}
		return extChars;
	}
});

