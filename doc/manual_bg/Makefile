# $Id$

LATEX    = latex
PDFLATEX = pdfelatex
PDFVIEW  = xpdf

all: pdf

pdf: lcm_manual_bg.latex
	${LATEX} lcm_manual_bg.latex
	dvips -Ppdf -G0 lcm_manual_bg.dvi -o lcm_manual_bg.ps
	ps2pdf -dPDFsettings=/prepress lcm_manual_bg.ps lcm_manual_bg.pdf

view: 
	${PDFVIEW} lcm_manual_bg.pdf

clean:
	-rm *.log
	-rm *.out
	-rm *.aux
	-rm *.toc
	-rm *.ps

vclean: clean
	-rm *.pdf
