# $Id$

MY_DIA = dia
MY_COMPRESS = pngcrush

DIA = $(shell which $(MY_DIA))
COMPRESS = $(shell which $(MY_COMPRESS))

all: database sitemap

database: database_png

sitemap: sitemap_png

database_png: lcm_database.dia
ifdef DIA
		@echo "** For dia->png, using: $(DIA)"
		$(DIA) --nosplash lcm_database.dia -e lcm_database.png -t png
else
		$(error "** Dia not found! Please install it or adjust this Makefile!")
		echo "allo"
endif
	

ifdef COMPRESS
		@echo "** For png->compress, using: $(COMPRESS)"
ifeq "$(MY_COMPRESS)" "pngcrush"
		$(COMPRESS) -e 2.png lcm_database.png
		mv lcm_database2.png lcm_database.png
else
		@echo "** I'm not sure how to use $(MY_COMPRESS), I will try without parameters"
		$(COMPRESS) lcm_database.png
endif
else
		$(warning "** Nothing found to compress, that's mothing bad, but pngcrush helps to generate small PNG files")
endif

	@echo "**"
	@echo "** All seems ok. End of line."
	@echo "**"

sitemap_png: lcm_sitemap.dia
ifdef DIA
		@echo "** For dia->png, using: $(DIA)"
		$(DIA) --nosplash lcm_sitemap.dia -e lcm_sitemap.png -t png
else
		$(error "** Dia not found! Please install it or adjust this Makefile!")
		echo "allo"
endif
	

ifdef COMPRESS
		@echo "** For png->compress, using: $(COMPRESS)"
ifeq "$(MY_COMPRESS)" "pngcrush"
		$(COMPRESS) -e 2.png lcm_sitemap.png
		mv lcm_sitemap2.png lcm_sitemap.png
else
		@echo "** I'm not sure how to use $(MY_COMPRESS), I will try without parameters"
		$(COMPRESS) lcm_sitemap.png
endif
else
		$(warning "** Nothing found to compress, that's mothing bad, but pngcrush helps to generate small PNG files")
endif

	@echo "**"
	@echo "** All seems ok. End of line."
	@echo "**"

clean:
	-rm *.png
