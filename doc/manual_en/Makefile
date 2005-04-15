# $Id$

LATEX    = latex
PDFLATEX = pdfelatex
PDFVIEW  = xpdf

all: pdf

pdf: lcm_manual_en.latex
	${LATEX} lcm_manual_en.latex
	dvips -Ppdf -G0 lcm_manual_en.dvi -o lcm_manual_en.ps
	ps2pdf -dPDFsettings=/prepress lcm_manual_en.ps lcm_manual_en.pdf

view: 
	${PDFVIEW} lcm_manual_en.pdf

clean:
	-rm *.log
	-rm *.out
	-rm *.aux
	-rm *.toc
	-rm *.ps

vclean: clean
	-rm *.pdf
