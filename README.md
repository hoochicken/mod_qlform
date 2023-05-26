# mod_qlform

The form generator is based on the J! way of generating forms.

For input data can be sent via e-mail. Even captcha is included and can be inserted - if wished.

The form generator mod_qlforms is based on the Joomla! xml. Any parameter (calendar, text email etc.) is at your service. Form fields provided by Joomla! are here: <http://docs.joomla.org/Category:Standard_form_field_types>

## What about a coffee ..

I love coding. My extensions are for free. Wanna say thanks? You're welcome! 
<https://www.buymeacoffee.com/mareikeRiegel>


## Basic Options

Here you add the xml code. Use Joomla! xml style for form. Params U see here. As textarea doesn't allow , use [ and ] instead. So the form xml might look like that.

~~~
[form]
  [fieldset name="fieldset1"]
    [field name="name" type="text"  size="50" required="true" /]
    [field name="email" type="email" size="50" required="true" /]
    [field name="subject" type="text" size="50" /]
    [field name="message" type="textarea" cols="50" rows="20" required="true" /]
  [/fieldset]
[/form]
~~~

You must use a form tag and a fieldset tag. Fieldset tag MUST have a name, otherwise Joomla! can't display fields

## Additional fieldsets

You can add additional fieldsets with (or without) labels, e. g.

~~~
[form]
  [fieldset name="fieldset1" label="Personal data"]
    [field name="firstname" type="text"  size="50" required="true" /]
    [field name="lastname" type="email" size="50" required="true" /]
    [field name="subject" type="text" size="50" /]
    [field name="message" type="textarea" cols="50" rows="20" required="true" /
  [/fieldset]
  [fieldset name="fieldset2" label="Adress data"]
    [field name="street" type="text"  size="50" required="true" /]
    [field name="suburb" type="text" size="50" required="true" /]
    [field name="zip" type="text" size="50" /]
  [/fieldset]
[/form]
~~~

## Add additional form fields

* Generate an overriding layout. Generate copies of all files that you can find in modules/mod_qlcontact/tmpl/...
* generate overriding folder
  * templates/yourTemplate/html/mod_qlcontact/
* Copy all .php-files into overriding template folder, e. g.:
  * templates/yourTemplate/html/mod_qlcontact/default.php
  * templates/yourTemplate/html/mod_qlcontact/default_data.php
  * ...
* Go to templates/yourTemplate/html/mod_qlcontact/default_form.php
  * Add your additional fields here in html.
  * Add verifications via html (attribute required="required", e. g. <input type="text" name="product" required="required" id="product" />)
  * Add verifications via Javascript (for devs who can code Javascript)
  * A lot of testing until the e-mail and the data sent delivers the data you want;-)
* And: you're lucky*, that's it

* *Fortunately mod_qlform sends  a l l  post data given to it, no further coding needed, strike! :-)

## Standard form field types

All form field types you find in the Joomla! docs:

http://docs.joomla.org/Category:Standard_form_field_types
http://docs.joomla.org/Standard_form_field_and_parameter_types

## Wellknown error message "invalid form"

In case you get the error message "invalid form" while saving. This refers not (!) to the xml in the textarea field. Just add a valid e-mail address in the e-mail address field.

By the way, this is why the param section "e-mail settings" is marked with an asterisk (*).

## Display mod_qlform in article

To display a mod_qlform or any other module in an article, do as follows:

* generate a mod_qlform; give it the position "qlform1"
* go to your article; enter {'loadposition qlform1} (without ' of course) into it
* that works already!
*  optimization: go to mod_qlform, change "Menu Assignement", activate module only for that very menu item, you call the article with (<-not neccassary, but nice:-)

Explanation: Joomla! has a plugin called up with the {'loadposition XXX} command (without ' of course). For "XXX" set the name for the module position you attach the module to; I always use something meaningful like "qlform1" or "contactform" a. s. o. You can call any module with this plugin command.

(Addition: Alternatively you could use the plg_qlmodule; that would make sense, if you have a bunch of forms that look all alike, but shall be sent to different e-mail recipient. For one or two forms the Joomla! core plugin is definately enough:-)

## Adding a text within form

Sometimes you wish to have an explaining text above or below a field. This can easily be done with a spacer field.

Just add the following tags within you xml:

~~~
[field type="spacer" label="This is an explaining text concerning the input below" description="This description text would show up when hovering the text"][/field]
~~~

More about spacers you find here: http://docs.joomla.org/Spacer_form_field_type

## Using the redirect function

* Create article like "Thank you very much for your message bla bla"
* Create "Shadow menu", it means a menu, that you will not display on website
* Create Menu entry in shadow menu link "thank you very much"
  * save
  * copy the url (here: "index.php/thank-you-very-much")
  * cancel
* Go to mod_qlform administration:
  * redirctions: use redirection: "yes"
  * redirctions: redirection: "/index.php/thank-you-very-much"
  * important!: leading "/" in front of "index.php"
  * save
* That should do it;-)

Explanation: After sending the mail (or doing whatever to the data) mod_qlform redirects to new page. This page is identified by it's url. As we want wo have a nice url ("/index.php/blabla") we create a menu entry in a shadow menu. Thus we avoid an seo unfriendly url like "indep.php?option=com_components&...". As it is only a shadow entry, it will be displayed nowhere.

## Cache and mod_qlform conflict

Symptom: Nothing happens; the captcha is static.

Probably the caching of the website is activated. Then the captcha pic won't be reloaded anymore.

**(1) For modules in a generic template module position**

Recommended setting:

* global configuration>caching:"no chaching"
* modules>mod_qlform>advanced>caching:"no"

**(2) For modules bound to an article via {'loadposition ...} or {'loadmodule ...} or so:**

Only one possible action: deactivate global configuration>caching:"no" and activate captcha

## Display form

Form settings>display form: "yes"

Redirection:"index.php/yourThankyoupage" (if you leave it blank, page will be empty aufter form submission)

## Changing input field size

(a) xml: add attribute size (sometimes not reliable)

Add the attribute "size" to your field - textareas require rows and cols attributes , e. g.:

~~~
[field type="text" size="20" ... /]
[field type="textarea" rows="20" cols="20" ... /]
~~~
Rating: Fast and easy, yet not reliable, as size attributes are interpreted differently by the browsers

(b) css: add stylesheets (always reliable, therefore recommended)

* Go to your stylesheets:
  * templates/yourTemplateName/css/yourCssFile.css or via Joomla!
* Extensions > Templates > Style > YourTemplateName > yourStylesheetFile.css
* Alter single, individual field:

~~~css
div.qlform #jform_name input {background:green; color:yellow;} /*input of field 'name'*/
~~~
If you want whole groups of inputs to look the same, call the via the fieldset they are in, like:

css
~~~css
#fieldset1 input {background:green; color:yellow;} /* all inputs in #fieldset1*/
~~~
Or call them via their class, that you have stated in the xml:

xml:
~~~
[field type="text" class="myGreenfields" ... /]
[field type="text" class="myGreenfields" ... /]
~~~
css:
~~~
div.myGreenFields  {background:green; color:yellow;} /* all inputs with class .myGreenFields*/
~~~

Rating: A little bit of work, but clean solution for every browser

## Using css styles on mod_qlform

mod_qlform generates regular html code. So use the normal css styles.
You can call the html code e. g. via following commands:

~~~css
div.qlform {} /*this div container around mod_qlform; depends on what name you gave it in the xml*/
div.qlform dt {} /*the dt container around the labels*/
div.qlform dd {} /*the dt container around the inputs*/
div.qlform dt.jform_name {} /*the very dt container of name field; depends on what name you gave it in the xml */
div.qlform dd.jform_name {} /*the very dt container of name field; depends on what name you gave it in the xml */
div.qlform input,div.qlform textarea, div.qlform button {} /*any button, textarea, input in mod_qlform*/
div.qlform .jform_name input {} /*input of field 'name'*/
~~~
Read also:

"Horizontal fields"
Important: User FireFox-Addon "Firebug", get it, you'll love it!

# Use Captcha

To use a captcha withing your module, do as follows:

**(1) Get captcha plugin extension**

* download any captcha plugin from http://extensions.joomla.org - of course I would recommend qlcaptcha;-)
* go to extensions> manage>install
* install the plugin

**(2) Configure captcha plugin**
 
* go to extension>plugins
* open theCaptchaPluginOfYourChoice
* adjust params of plugin
* save

**(3) Joomla! configuration**
* go to System>configuration
* find parameter "Captcha"
* select the captcha of your choice
* save

**(4) Module params**
* go to extensions>modules
* open mod_qlform
* go to captcha tab
* set parameter "Captcha" to "yes"
* save, DONE

## Horizontal fields by bootstrap

Try as follows (works in bootstrap template):

~~~
  [fieldset name="someName" class="row-fluid"]
    [field class="span6" name="someFieldname1" type="text" /]
    [field class="span6" name="someFieldname2" type="text" /]
  [/fieldset]
~~~

Explanation: In bootstrap the page is virually divided in 12 columns. The "span6" class ist 6 columns long; so span6 + span 6 adds up to 12; or span4+span2+span6. Each row is seated in a fieldset with a css class "row-fluid".

## Horizontal fields

It's all about css stuff; the basic css comand needed are as follows:

~~~css
float:left; /* the next element shall be to my left*/
clear:both; /* I will stand in the next line, I don't care what my pregoing elemnt wants  */
width: 150px; /*oder*/ width: 250px; /*often elements would like to float,
~~~
but are too big; so cut down the size a little*/

Helpful: FireFox-Addon "Firebug", get it, you'll love it!

2 Possibilities for horizontal fields

**(6.1) via css**

Every form field and every label has an own id or other html attribute. They are accessible via css.

Example xml:
~~~
[field name="zutaten" label="Checkboxes" type="checkboxes"]
[option value="1"]Salami[/option]
[option value="2"]Champignons[/option]
[option value="3"]Käse extra[/option]
[option value="4"]Noch mehr Käse[/option]
[/field]
~~~
css commands:
~~~css
#jform_zutaten li {float:left; display:block; width:200px; height:50px;}
#jform_zutaten li:nth-child(2n-1) {clear:both;} /*that means, every 1st,, 3rd, 5th etc. field ist displayed in a new line; works in FireFox and IE9; might not work on IE8.*/
~~~
Where to find css files? Go to the css files of your template. You find them here:

a) either via files: templates/yourTemplateName/css/styleSheetName.css
b) or via Joomla administration: Extensions>Template Manager>Templates>yourTemplateName Details and Files>styleSheetName.css

**(6.2) Via html**

(Rather for developers.) Generate an overriding layout. Generate copies of all files that you can find in modules/mod_qlform/tmpl/..., e. g.:

* templates/yourTemplate/html/mod_qlform/default.php
* templates/yourTemplate/html/mod_qlform/default_formPurehtml.php
* templates/yourTemplate/html/mod_qlform/default_formBootstrap.php
* ...

In the module params (tab "styles") you have chosen whether you want to have pure html or a bootstrap form. (Default setting ist pure html.) According to this param adjust one of the following files:

* templates/yourTemplate/html/mod_qlform/default_formPurehtml.php
* templates/yourTemplate/html/mod_qlform/default_formBootstrap.php

In the original files the form ist generate automatically via ->getFieldsetsWe're gonna change this now.

The field should be available as separate fields (that means e. g. not one "checkboxes" field, but several "checkbox" fields. Now you can generate labels an inputs via following commands:

~~~php
<?php
echo $form->getInput('fieldId');
echo $form->getLabel('fieldId');
~~~

These commands you can a. g. put into a table.

Example xml:
~~~
    [field name="salami" id="salami" label="Salami" type="checkbox" /]
    [field name="champignons" id="champignons" label="Champignons" type="checkbox" /]
    [field name="kaese-plus" id="kaese-plus" label="Käse extra" type="checkbox" /]
    [field name="kaese-doppelplus" id="kaese-doppelplus" label="Noch mehr Käse" type="checkbox" /]
~~~
The correspanding html

~~~html
<table>
<tr><?php echo $form->getInput('fieldId');?><td><?php echo $form->getInput('fieldId');?></td></tr>
<tr><?php echo $form->getInput('fieldId2');?><td><?php echo $form->getInput('fieldId2');?></td></tr>
</table>
~~~

## Prefilled data in form

mod_qlform can do this, if you use an override or - even better - an alternative layout.
Do as follows:

**(1) Create alternative layout**

* go to modules/mod_qlform/tmpl/
* copy all the files in this folder to templates/yourTemplateName/tmpl/mod_qlform/
* rename the files from "default" to "prefilled"
* go to "prefilled.php" and replace "default" by "prefilled"

**(2) Activate alternative layout for 2nd form (with the prefilled data)**

* J! backend: modules>prefilled module>advanced settings>
* choose here layout "prefilled"

**(3) Get data**

* go to templates/yourTemplateName/html/mod_qlform/prefilled_form.php
* add the following 3 bold lines after the "defined('_JEXEC') or die;" line, about line 10: 

~~~php
<?php
#either hardcoded data
$dataToBind=new stdClass;
$dataToBind->name='Hungry Hamster';
$dataToBind->street='Grain Street 14';
$dataToBind->city='Corn field';

# or post data
$dataToBind=new stdClass;
if (isset($_POST['jform'])) foreach ($_POST['jform'] as $k=>$v) $dataToBind->$k=$v;
~~~

**(4) Bind data to form**

* go to templates/yourTemplateName/html/mod_qlform/prefilled_form.php
* add following line behind your data generation

~~~php
<?php
$form->bind($dataToBind);
~~~
That's it, three lines!

## Save to database
 
**1. SETTINGS**

Use following settings (example here with recipe table):

* Basic Options > Save to Database : Yes
* Database > Database table : #__recipes [put here your table's name)
* Database > Add date created : Yes

**2. XML**

Say, we have a form for recipes people can send. XML looks like that:
~~~
[form]
[fieldset name="fieldset1"]
[field name="name" type="text"  size="50" required="true" /]
[field name="ingredients" type="textarea" size="50" cols="50" rows="20" required="true" /]
[field name="todo" type="textarea" size="50" cols="50" rows="20" required="true" /]
[/fieldset]
[/form]
~~~

**3. CREATE TABLE**

Create the table #__recipes in your database. This table has the following cols:

* id [autoincrement; important!]
* name
* ingredients
* todo
* created

**4. TROUBLE SHOOTING**

The module itself tells you, what it needs. Do as follows:

* Basic Options > Save to Database : Yes
* Basic Options > Message type : In module itself
* Database > Show db-form conflict : Yes
* Cascading forms > Add post data to form : No [default of elder versions was "yes", but might lead to problems here]

**5. READ ERROR MESSAGE AND ADJUST**

Now submit your form and see, what the form tells you. E. g. my qlform tells me now the following error message

"Table `fwj25_qlform_survey1` was not found in database `snoopy`. To store data, create new table in database.
For help check module parameters and read description of module.
mod_qlform : Db-form conflict:

* `name` column could not be found in database.
* `email` column could not be found in database.
* `subject` column could not be found in database.
* `message` column could not be found in database.

If intended, ignore message. (Tip: Set 'Display db-form conflict' to 'No' before going online.)"

Here you can see right away, what's missing. In my case, the table is not found (therefore no table cols are found, too:-).

## Preprocess data

Available since version 10.1.3

To preprocess data before storing to database or sending with e-mail etc, the data can be preprocessedd, To alter data before any further actions, the following actions have to be performed:

**module configuration**
* go to extensions > modules > mod_qlform
* set parameter "preprocess data" to "yes"

**file structrure**
* go to your file system
* go to modules/mod_qlform/php/classes/
* rename "modQlformPreprocessData.php-rename-me" to "modQlformPreprocessData.php"
* add your code to alter data
* Several methods whose names are pretty well self-explanatory are available. Their structure ist quite simple: they retrieve data and return it. Further more, there are class variables like the module params, and the form object.

~~~php
<?php
formDataBeforeValidation($data)
formAndFileDataBeforeValidation($data)
email($data)
database($data)
databaseExternal($data)
somethingElse($data)
completlyDifferent($data)
jmessage($data)
sendcopy($data)
addionally you find the neat method p() with the following code:
function p($data,$die=1)
{
echo '<pre>';print_r($data);echo '</pre>';
if(1==$die)die('kia');
}
~~~

