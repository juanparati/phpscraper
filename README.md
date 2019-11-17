PHPSCRAPER
==========

## 1. What is it?

A command line tool used for extract and format content from webpages. It's suitable for extract like:
- product catalogs
- reviews
- lists
- etc

The output is formatted as [JSON lines](http://jsonlines.org/).


## 2. How it works?

1. Create a scraper receipt (see [recipes](recipes))
2. Type:

        phpscraper config url
        

The following example will extract all the reviews with the user name, comment and rating from Amazon:

        phpscraper recipes/amazon.yml https://www.amazon.de/product-reviews/B000J34HN4/ref=acr_dpx_hist_3?ie=UTF8

For see additional options just type:

        phpscraper --help       



## 3. Recipes

Recipes are YML files that describe in a structure way how to extract the content from the pages. The recipes uses [XPath](https://www.w3schools.com/xml/xpath_intro.asp) routes in order to instruct which elements are extracted.

Example of recipe that extract comments from Amazon reviews:

        project: "Amazon reviews extractor"
        pagination:
          next_xpath: "//li[@class='a-last']/a/@href"
        extraction:
          product:
            xpath: "//h1/a[@class='a-link-normal']"
            extract_as: "product"
            in_memory: true
          comments:
            xpath: "//div[@class='a-section celwidget']"
            subelements:
              product:
                from_memory: "product"
                extract_as: "product"
              name:
                xpath: "//span[@class='a-profile-name']"
                extract_as: "name"
              rate:
                xpath: "//i[contains(@class, 'a-icon-star')]/span"
                extract_as: "rating"
                extract_regex: "/^.{0,3}/"
                cast_as: float
              comment:
                xpath: "//span[@class='a-size-base review-text review-text-content']"
                extract_as: "comment"
              verified:
                xpath: "//span[@class='a-size-mini a-color-state a-text-bold']"
                extract_as: "verified"
                cast_as: boolean
                
                
### 3.1 The pagination section
 
It defines where the "next page" button is located. In case that this element is not found then scraper then it will finish the process until the current page is extracted.
 
 
### 3.2 The extraction section
 
It defines which elements are going to be extracted. It uses a cascade structure so its possible to define the parent and child elements.
 
The possible instructions for the extraction section are:

- xpath: It defines the element that is going to be extracted using a XPath expression. The expression will become automatically relative to the parent element if exists.
- subelements: It defines the child elements.
- extract_as: This is always required and it sets the field name of the element to extract.
- extract_regex: Regular expression used for extract the extracted content.
- cast_as: Cast the extracted data, so the JSON output will reflect the right data type. Possible casts are "boolean", "int", "float" and "string"
- in_memory: It used when we want to save temporally in the memory so we can use for example inside of another extraction thread.


## 4. Installation

PHPscraper can be installed in different ways:

A) Download the [last build from Github](https://github.com/juanparati/phpscraper/releases/latest)
or
B) Just type "composer global require juanparati/phpscraper"


## 5. How to build my own package:

* [Download Caveman](https://github.com/Mamuph/caveman/releases) (The Mamuph Helper Tool)
* Clone this project
* Inside the project directory type:

        caveman build . -x -r
