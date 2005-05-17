# $Id$

#LATEX    = latex
LATEX    = pdflatex
PDFLATEX = pdfelatex
PDFVIEW  = xpdf

all: pdf

pdf: lcm_manual_en.latex
	${LATEX} lcm_manual_en.latex
#	dvips -Ppdf -G0 lcm_manual_en.dvi -o lcm_manual_en.ps
#	ps2pdf -dPDFsettings=/prepress lcm_manual_en.ps lcm_manual_en.pdf

view: 
	${PDFVIEW} lcm_manual_en.pdf

html_en:
	latex2html -init_file=../latex2html-init -html_version 4.0,unicode,unicode  lcm_manual_en.latex 
	sh ../lcm_clean_html.sh en /var/www/legalcase/inc/help/

clean:
	-rm -f *.log
	-rm -f *.out
	-rm -f *.aux
	-rm -f *.toc
	-rm -f *.ps

vclean: clean
	-rm -f *.pdf
