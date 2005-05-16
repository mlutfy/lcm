# $Id$

#LATEX    = latex
LATEX    = pdflatex
PDFLATEX = pdfelatex
PDFVIEW  = xpdf

all: pdf

pdf: lcm_manual_bg.latex
	${LATEX} lcm_manual_bg.latex
#	dvips -Ppdf -G0 lcm_manual_bg.dvi -o lcm_manual_bg.ps
#	ps2pdf -dPDFsettings=/prepress lcm_manual_bg.ps lcm_manual_bg.pdf

view: 
	${PDFVIEW} lcm_manual_bg.pdf

html_bg:
	latex2html -init_file=../latex2html-init -html_version 4.0,unicode,unicode  lcm_manual_bg.latex 
	sh ../lcm_clean_html.sh bg /var/www/legalcase/inc/help/

clean:
	rm -f *.log
	rm -f *.out
	rm -f *.aux
	rm -f *.toc
	rm -f *.ps

vclean: clean
	rm -f *.pdf
	rm -f *.dvi
